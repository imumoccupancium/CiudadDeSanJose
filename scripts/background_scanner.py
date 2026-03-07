import sys
import time
import requests
import win32gui
import win32api
import win32con
import struct
import ctypes
from ctypes import wintypes

# --- CONFIGURATION ---
API_URL = "http://localhost/CiudadDeSanJose/admin/api/auto_scan.php"

# Map your Hardware IDs here
# You can swap these if the Entry/Exit is reversed
SCANNERS = {
    "INSIDE": "1a2fac91",  # Unang ID na binigay mo
    "OUTSIDE": "18e60f12"  # Pangalawang ID na binigay mo
}

MIN_CHARS = 5
MAX_TIME_BETWEEN = 0.05 # 50ms - Detects fast hardware input

# --- CONSTANTS ---
WM_INPUT = 0x00FF
RID_INPUT = 0x10000003
RIM_TYPEKEYBOARD = 1

class RAWINPUTHEADER(ctypes.Structure):
    _fields_ = [("dwType", wintypes.DWORD), ("dwSize", wintypes.DWORD), ("hDevice", wintypes.HANDLE), ("wParam", wintypes.WPARAM)]

class RAWKEYBOARD(ctypes.Structure):
    _fields_ = [("MakeCode", wintypes.USHORT), ("Flags", wintypes.USHORT), ("Reserved", wintypes.USHORT), ("VKey", wintypes.USHORT), ("Message", wintypes.UINT), ("ExtraInformation", wintypes.ULONG)]

class RAWINPUT(ctypes.Structure):
    class _U(ctypes.Union):
        _fields_ = [("keyboard", RAWKEYBOARD)]
    _fields_ = [("header", RAWINPUTHEADER), ("data", _U)]

class MultiScannerSystem:
    def __init__(self):
        self.buffers = {} # {hDevice: {"text": "", "last_time": 0}}
        self.device_map = {} # Cache for hDevice -> "INSIDE"|"OUTSIDE"
        
        print("--- Ciudad De San Jose: Multi-Scanner System ---")
        print(f"Inside Scanner: ...{SCANNERS['INSIDE']}...")
        print(f"Outside Scanner: ...{SCANNERS['OUTSIDE']}...")
        print("Ready. Listening for scans...\n")

    def get_device_info(self, h_device):
        if h_device in self.device_map:
            return self.device_map[h_device]
        
        size = wintypes.UINT()
        ctypes.windll.user32.GetRawInputDeviceInfoW(h_device, 0x20000007, None, ctypes.byref(size))
        name_buffer = ctypes.create_unicode_buffer(size.value)
        ctypes.windll.user32.GetRawInputDeviceInfoW(h_device, 0x20000007, name_buffer, ctypes.byref(size))
        path = name_buffer.value.upper()

        if SCANNERS['INSIDE'].upper() in path:
            self.device_map[h_device] = "INSIDE"
        elif SCANNERS['OUTSIDE'].upper() in path:
            self.device_map[h_device] = "OUTSIDE"
        else:
            self.device_map[h_device] = "UNKNOWN"
            
        return self.device_map[h_device]

    def handle_keypress(self, h_device, vkey):
        role = self.get_device_info(h_device)
        if role == "UNKNOWN":
            return # Ignore regular keyboard input

        if h_device not in self.buffers:
            self.buffers[h_device] = {"text": "", "last_time": time.time()}

        buf = self.buffers[h_device]
        current_time = time.time()
        time_diff = current_time - buf['last_time']
        buf['last_time'] = current_time

        # If it's the Enter key
        if vkey == win32con.VK_RETURN:
            if len(buf['text']) >= MIN_CHARS:
                self.trigger_api(buf['text'], role)
            buf['text'] = ""
            return

        # Convert Virtual Key to Character
        char = chr(win32api.MapVirtualKey(vkey, 2))
        
        # Check if input is fast (likely scanner)
        if time_diff < MAX_TIME_BETWEEN or buf['text'] == "":
            buf['text'] += char
        else:
            buf['text'] = char # Reset if too slow

    def trigger_api(self, code, role):
        action = "IN" if role == "INSIDE" else "OUT"
        device_name = f"{role} Gate Scanner"
        
        print(f"[{time.strftime('%H:%M:%S')}] {role} SCAN: '{code}'")
        
        try:
            payload = {
                'token': code,
                'action': action,
                'device_name': device_name
            }
            response = requests.post(API_URL, data=payload, timeout=5)
            if response.status_code == 200:
                result = response.json()
                if result.get('success'):
                    print(f"  \033[92mSUCCESS: {result.get('message')}\033[0m")
                else:
                    print(f"  \033[91mDENIED: {result.get('message')}\033[0m")
            else:
                print(f"  SERVER ERROR: HTTP {response.status_code}")
        except Exception as e:
            print(f"  CONNECTION FAILED: {e}")

def wnd_proc(hwnd, msg, wparam, lparam):
    if msg == WM_INPUT:
        size = wintypes.UINT()
        ctypes.windll.user32.GetRawInputData(lparam, RID_INPUT, None, ctypes.byref(size), ctypes.sizeof(RAWINPUTHEADER))
        raw = RAWINPUT()
        if ctypes.windll.user32.GetRawInputData(lparam, RID_INPUT, ctypes.byref(raw), ctypes.byref(size), ctypes.sizeof(RAWINPUTHEADER)):
            if raw.header.dwType == RIM_TYPEKEYBOARD and raw.data.keyboard.Message == win32con.WM_KEYDOWN:
                app.handle_keypress(raw.header.hDevice, raw.data.keyboard.VKey)
    return win32gui.DefWindowProc(hwnd, msg, wparam, lparam)

def register_devices(hwnd):
    class RAWINPUTDEVICE(ctypes.Structure):
        _fields_ = [("usUsagePage", wintypes.USHORT), ("usUsage", wintypes.USHORT), ("dwFlags", wintypes.DWORD), ("hwndTarget", wintypes.HWND)]
    dev = RAWINPUTDEVICE(0x01, 0x06, 0x00000100, hwnd) # Keyboard, InputSink
    ctypes.windll.user32.RegisterRawInputDevices(ctypes.byref(dev), 1, ctypes.sizeof(dev))

if __name__ == "__main__":
    app = MultiScannerSystem()
    wc = win32gui.WNDCLASS()
    wc.lpfnWndProc = wnd_proc
    wc.lpszClassName = "ScannerApp"
    wc.hInstance = win32api.GetModuleHandle(None)
    class_atom = win32gui.RegisterClass(wc)
    hwnd = win32gui.CreateWindow(class_atom, "Scanner Service", 0, 0, 0, 0, 0, 0, 0, wc.hInstance, None)
    register_devices(hwnd)
    
    try:
        win32gui.PumpMessages()
    except KeyboardInterrupt:
        print("\nExiting...")
        sys.exit(0)
