import urllib.request
import urllib.parse
import threading
import time
import struct
import socket
import sqlite3
import json
import os
from datetime import datetime

# Try to import requests for better performance (connection pooling)
try:
    import requests
    from requests.adapters import HTTPAdapter
    HAS_REQUESTS = True
    session = requests.Session()
    # Keep TCP connections alive across scans — avoids repeated TLS handshakes
    adapter = HTTPAdapter(
        pool_connections=2,
        pool_maxsize=10,
        max_retries=0
    )
    session.mount('https://', adapter)
    session.mount('http://', adapter)
    session.headers.update({
        'Connection': 'keep-alive',
        'User-Agent': 'Mozilla/5.0 (SmartRelay v2.1-Offline)'
    })
except ImportError:
    HAS_REQUESTS = False

# =============================================================
# CONFIGURATION
# =============================================================
DOMAIN  = "ciudaddesanjose.site"
API_URL = f"https://{DOMAIN}/admin/api/auto_scan.php"

# Offline Sync API Endpoints
CACHE_URL      = f"https://{DOMAIN}/admin/api/get_token_cache.php"
SYNC_LOGS_URL  = f"https://{DOMAIN}/admin/api/sync_offline_logs.php"

# ACM-WEG 04 CONFIGURATION
ACM_IP = "192.168.30.250"
ACM_SN = 425048688

# Local SQLite database file (stored next to this script)
DB_PATH = os.path.join(os.path.dirname(os.path.abspath(__file__)), "local_scanner.db")

# How often (in seconds) to sync the local cache with the server
SYNC_INTERVAL_SECONDS = 30  # 10 seconds

# How long to wait for the internet API before falling back (seconds)
INTERNET_TIMEOUT = 4

# In-memory status mirror — updated immediately after every scan
# Prevents stale-cache conflicts between sync intervals
_status_cache = {}  # { token: 'IN' | 'OUT' }
_status_lock   = threading.Lock()

SCANNERS = {
    "/dev/input/by-id/usb-Newland_Auto-ID_NLS_IOTC_PRDs_HID_KBW_FCEM5088-event-kbd":
        {"name": "Entrance Gate", "action": "IN",  "door": 1},

    "/dev/input/by-id/usb-Newland_Auto-ID_NLS_IOTC_PRDs_HID_KBW_FCEM5150-event-kbd":
        {"name": "Exit Gate",     "action": "OUT", "door": 2},
}

# =============================================================
# KEY MAPPING
# =============================================================
key_map = {
    2:'1', 3:'2', 4:'3', 5:'4', 6:'5', 7:'6', 8:'7', 9:'8', 10:'9', 11:'0',
    12:'-',
    16:'Q', 17:'W', 18:'E', 19:'R', 20:'T', 21:'Y', 22:'U', 23:'I', 24:'O', 25:'P',
    30:'A', 31:'S', 32:'D', 33:'F', 34:'G', 35:'H', 36:'J', 37:'K', 38:'L',
    44:'Z', 45:'X', 46:'C', 47:'V', 48:'B', 49:'N', 50:'M',
}

# =============================================================
# LOCAL SQLite DATABASE SETUP
# =============================================================
def init_local_db():
    """Create the local SQLite database tables if they don't exist."""
    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()

    # Table: cached_tokens — local copy of valid QR codes
    c.execute("""
        CREATE TABLE IF NOT EXISTS cached_tokens (
            qr_token        TEXT PRIMARY KEY,
            user_internal_id INTEGER NOT NULL,
            homeowner_id    TEXT NOT NULL,
            name            TEXT NOT NULL,
            current_status  TEXT DEFAULT 'OUT',
            user_type       TEXT NOT NULL,
            synced_at       TEXT
        )
    """)

    # Table: offline_logs — scans done while internet was down
    c.execute("""
        CREATE TABLE IF NOT EXISTS offline_logs (
            id              INTEGER PRIMARY KEY AUTOINCREMENT,
            user_internal_id INTEGER NOT NULL,
            homeowner_id    TEXT NOT NULL,
            name            TEXT NOT NULL,
            user_type       TEXT NOT NULL,
            action          TEXT NOT NULL,
            timestamp       TEXT NOT NULL,
            device_name     TEXT NOT NULL,
            synced          INTEGER DEFAULT 0
        )
    """)

    conn.commit()
    conn.close()
    print("[LOCAL DB] SQLite database initialized.")

# =============================================================
# ACM GATE CONTROL
# =============================================================
def open_acm_gate(door_index=1):
    try:
        sn_hex = f"{ACM_SN:08x}"
        packet = bytearray(64)
        packet[0] = 0x17
        packet[1] = 0x40
        packet[4:8] = bytearray.fromhex(sn_hex)[::-1]
        packet[8] = door_index
        sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        sock.sendto(packet, (ACM_IP, 60000))
        print(f"   [ACM-WEG] GATE SIGNAL SENT (Door {door_index})")
        sock.close()
    except Exception as e:
        print(f"   [ACM-WEG] FAILED: {e}")

# =============================================================
# INTERNET-FIRST SCAN HANDLER
# =============================================================
# Simple debounce: { (token, action): timestamp } to prevent rapid duplicate scans
_last_scans = {}
_scan_lock  = threading.Lock()

def send_data(token, action, device_name, door_index):
    token = token.strip().upper()

    # -------------------------------------------------------
    # DEBOUNCE: Skip if scanned at this same gate in the last 2 seconds
    # -------------------------------------------------------
    now = time.time()
    with _scan_lock:
        key = (token, action)
        if key in _last_scans and (now - _last_scans[key]) < 2:
            print(f"   [SKIP]    Hardware double-scan detected for {token}. Ignoring duplicate.")
            return
        _last_scans[key] = now

    print(f"\n[{action}] QR: {token} — Device: {device_name}")
    start_time = time.time()

    payload = {'token': token, 'action': action, 'device_name': device_name}
    headers = {}  # session already has keep-alive + User-Agent set globally

    # -------------------------------------------------------
    # STEP 1: Try the Internet (Primary)
    # -------------------------------------------------------
    try:
        if HAS_REQUESTS:
            response = session.post(API_URL, data=payload, timeout=INTERNET_TIMEOUT)
            response_text = response.text
        else:
            encoded = urllib.parse.urlencode(payload).encode()
            req  = urllib.request.Request(API_URL, data=encoded, headers={'User-Agent': 'Mozilla/5.0 (SmartRelay v2.1-Offline)'})
            with urllib.request.urlopen(req, timeout=INTERNET_TIMEOUT) as res:
                response_text = res.read().decode()

        latency = (time.time() - start_time) * 1000
        print(f"   [ONLINE]  Server replied in {latency:.0f}ms: {response_text.strip()}")

        if '"success":true' in response_text:
            print(f"   [VALID]   Access Granted. Opening Door {door_index}...")
            open_acm_gate(door_index)

            # Mirror status in memory immediately (no SQLite round-trip needed)
            with _status_lock:
                _status_cache[token] = action
            # Also persist to local SQLite cache (non-blocking)
            threading.Thread(target=update_local_status, args=(token, action), daemon=True).start()
        else:
            print(f"   [DENIED]  Server denied access.")

        return  # Done — internet handled it

    except Exception as e:
        latency = (time.time() - start_time) * 1000
        print(f"   [OFFLINE] Internet unreachable after {latency:.0f}ms ({e}). Switching to local database...")

    # -------------------------------------------------------
    # STEP 2: Fallback to Local SQLite Cache
    # -------------------------------------------------------
    try:
        conn = sqlite3.connect(DB_PATH)
        conn.row_factory = sqlite3.Row
        c = conn.cursor()

        c.execute("SELECT * FROM cached_tokens WHERE qr_token = ?", (token,))
        user = c.fetchone()

        if not user:
            print(f"   [LOCAL]   QR Token not found in local cache. Access DENIED.")
            conn.close()
            return

        current_status = user['current_status'] or 'OUT'

        # Prevent duplicate IN/OUT
        if action == current_status:
            msg = "Already INSIDE." if action == 'IN' else "Already OUTSIDE."
            print(f"   [LOCAL]   Status conflict — {msg} Access DENIED.")
            conn.close()
            return

        # Grant access
        print(f"   [LOCAL]   Found: {user['name']} ({user['user_type']}). Access GRANTED (offline).")
        open_acm_gate(door_index)

        # Update local status
        now = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        c.execute("UPDATE cached_tokens SET current_status = ? WHERE qr_token = ?", (action, token))

        # Save to offline log for later sync
        c.execute("""
            INSERT INTO offline_logs
                (user_internal_id, homeowner_id, name, user_type, action, timestamp, device_name)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        """, (
            user['user_internal_id'],
            user['homeowner_id'],
            user['name'],
            user['user_type'],
            action,
            now,
            device_name
        ))

        conn.commit()
        conn.close()
        print(f"   [LOCAL]   Offline log saved. Will sync when internet returns.")

    except Exception as e:
        print(f"   [LOCAL]   Local DB error: {e}")

# =============================================================
# LOCAL CACHE HELPERS
# =============================================================
def update_local_status(token, new_status):
    """Update the current_status in local cache after a successful online scan."""
    try:
        conn = sqlite3.connect(DB_PATH)
        conn.execute("UPDATE cached_tokens SET current_status = ? WHERE qr_token = ?", (new_status, token))
        conn.commit()
        conn.close()
    except Exception:
        pass  # Non-critical, local cache update failure is acceptable

# =============================================================
# BACKGROUND SYNC THREAD
# =============================================================
def sync_thread():
    """
    Runs in the background every SYNC_INTERVAL_SECONDS.
    1. Downloads fresh QR token list from server (PULL).
    2. Uploads pending offline logs to server (PUSH).
    """
    print(f"[SYNC] Background sync thread started. Interval: {SYNC_INTERVAL_SECONDS}s")
    while True:
        time.sleep(SYNC_INTERVAL_SECONDS)
        print(f"\n[SYNC] Starting scheduled sync...")
        pull_token_cache()
        push_offline_logs()

def pull_token_cache():
    """Download fresh active tokens from the server and rebuild local cache."""
    print("[SYNC] Pulling token cache from server...")
    try:
        if HAS_REQUESTS:
            response = session.get(CACHE_URL, timeout=15)
            data = response.json()
        else:
            with urllib.request.urlopen(CACHE_URL, timeout=15) as res:
                data = json.loads(res.read().decode())

        if not data.get('success'):
            print(f"[SYNC] Pull failed: {data.get('message')}")
            return

        tokens = data.get('tokens', [])
        synced_at = data.get('synced_at', datetime.now().strftime('%Y-%m-%d %H:%M:%S'))

        conn = sqlite3.connect(DB_PATH)
        c = conn.cursor()

        # Clear old cache and rebuild (fast full refresh)
        c.execute("DELETE FROM cached_tokens")
        for t in tokens:
            # Force token to uppercase to match scanner input consistency
            upper_token = str(t['qr_token']).strip().upper()
            c.execute("""
                INSERT OR REPLACE INTO cached_tokens
                    (qr_token, user_internal_id, homeowner_id, name, current_status, user_type, synced_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            """, (
                upper_token,
                t['user_internal_id'],
                t['homeowner_id'],
                t['name'],
                t.get('current_status', 'OUT'),
                t['user_type'],
                synced_at
            ))

        conn.commit()
        conn.close()
        print(f"[SYNC] Token cache updated. {len(tokens)} tokens cached locally.")

    except Exception as e:
        print(f"[SYNC] Pull error: {e}")

def push_offline_logs():
    """Upload any pending offline logs to the server and mark them as synced."""
    print("[SYNC] Checking for pending offline logs...")
    try:
        conn = sqlite3.connect(DB_PATH)
        conn.row_factory = sqlite3.Row
        c = conn.cursor()

        c.execute("SELECT * FROM offline_logs WHERE synced = 0")
        pending = c.fetchall()

        if not pending:
            print("[SYNC] No pending offline logs. All clear.")
            conn.close()
            return

        print(f"[SYNC] Found {len(pending)} pending log(s) to upload...")
        payload = []
        ids     = []
        for row in pending:
            payload.append({
                'user_internal_id': row['user_internal_id'],
                'homeowner_id':     row['homeowner_id'],
                'name':             row['name'],
                'user_type':        row['user_type'],
                'action':           row['action'],
                'timestamp':        row['timestamp'],
                'device_name':      row['device_name'],
            })
            ids.append(row['id'])

        json_payload = json.dumps(payload).encode('utf-8')
        headers = {
            'Content-Type': 'application/json',
            'User-Agent': 'Mozilla/5.0 (SmartRelay v2.1-Offline)'
        }

        if HAS_REQUESTS:
            response = session.post(SYNC_LOGS_URL, data=json_payload, headers=headers, timeout=15)
            result = response.json()
        else:
            req = urllib.request.Request(SYNC_LOGS_URL, data=json_payload, headers=headers)
            with urllib.request.urlopen(req, timeout=15) as res:
                result = json.loads(res.read().decode())

        if result.get('success'):
            # Mark synced
            c.executemany("UPDATE offline_logs SET synced = 1 WHERE id = ?", [(i,) for i in ids])
            conn.commit()
            print(f"[SYNC] Upload complete. {result.get('processed', 0)} logs synced to server.")
        else:
            print(f"[SYNC] Upload failed: {result.get('message')}")

        conn.close()

    except Exception as e:
        print(f"[SYNC] Push error: {e}")

# =============================================================
# QR SCANNER MONITOR
# =============================================================
def monitor_scanner(device_path, config):
    print(f"[*] Monitoring: {config['name']} ({device_path})")
    qr_buffer  = ""
    FORMAT     = 'llHHi'
    EVENT_SIZE = struct.calcsize(FORMAT)
    try:
        with open(device_path, "rb") as f:
            while True:
                data = f.read(EVENT_SIZE)
                if not data or len(data) < EVENT_SIZE:
                    continue
                (tv_sec, tv_usec, ev_type, ev_code, ev_value) = struct.unpack(FORMAT, data)

                if ev_type == 1 and ev_value == 1:  # Key Press
                    if ev_code == 28:  # ENTER key
                        if qr_buffer:
                            threading.Thread(
                                target=send_data,
                                args=(qr_buffer, config['action'], config['name'], config['door']),
                                daemon=True
                            ).start()
                            qr_buffer = ""
                    elif ev_code in key_map:
                        qr_buffer += key_map[ev_code]
    except Exception as e:
        print(f"[!] {config['name']} error: {e}")

# =============================================================
# ENTRY POINT
# =============================================================
if __name__ == "__main__":
    print("=" * 55)
    print("  Ciudad De San Jose: Smart Relay System v2.1")
    print(f"  Server : {DOMAIN}")
    print(f"  ACM    : {ACM_IP}")
    print(f"  DB     : {DB_PATH}")
    print("=" * 55)

    if HAS_REQUESTS:
        print("[INFO] Performance: requests session active (fast)")
    else:
        print("[WARN] requests not found. Using urllib (slower). Run: pip install requests")

    # 1. Initialize local SQLite DB
    init_local_db()

    # 2. Do an initial token cache pull on startup
    print("[INIT] Performing initial token cache pull...")
    pull_token_cache()

    # 3. Start background sync thread
    t_sync = threading.Thread(target=sync_thread, daemon=True)
    t_sync.start()

    # 4. Start scanner monitor threads
    for path, config in SCANNERS.items():
        t = threading.Thread(target=monitor_scanner, args=(path, config), daemon=True)
        t.start()

    print("-" * 55)
    print("[READY] System is ONLINE. Scanning...")
    print("[MODE]  Internet-First | Local Fallback Active")
    print("-" * 55)

    try:
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        print("\n[EXIT] Shutting down gracefully...")
