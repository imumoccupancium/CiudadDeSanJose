import sys
import time
import requests
from pynput import keyboard

# CONFIGURATION
# Modify this URL if your project setup is different
API_URL = "http://localhost/CiudadDeSanJose/admin/api/auto_scan.php"
MIN_CHARS = 5
MAX_TIME_BETWEEN = 0.05  # 50ms (Scanners are much faster than humans)

class BackgroundScanner:
    def __init__(self):
        self.buffer = ""
        self.last_time = time.time()
        print(f"--- Background QR Scanner Started ---")
        print(f"Target API: {API_URL}")
        print(f"Listening for hardware scanner input...")
        print(f"Press Ctrl+C to stop.")

    def on_press(self, key):
        current_time = time.time()
        time_diff = current_time - self.last_time
        self.last_time = current_time

        try:
            # Check if it's a character or Enter
            if hasattr(key, 'char') and key.char is not None:
                char = key.char
            elif key == keyboard.Key.enter:
                char = "ENTER"
            else:
                return

            # Logic to detect if it's a scanner (fast input)
            # Or if it's the start of a buffer
            if time_diff < MAX_TIME_BETWEEN or self.buffer == "":
                if char == "ENTER":
                    if len(self.buffer) >= MIN_CHARS:
                        self.trigger_scan(self.buffer)
                    self.buffer = ""
                else:
                    self.buffer += char
            else:
                # Too slow, likely a human typing, reset buffer
                if len(self.buffer) > 0:
                    # If human types 'Enter', we don't want to process the old buffer
                    self.buffer = ""
                
                # However, if this is the start of a new (fast) sequence, it'll be caught next time
                if char != "ENTER":
                    self.buffer = char

        except Exception as e:
            print(f"Error: {e}")

    def trigger_scan(self, code):
        print(f"\n[SCAN DETECTED] Code: {code}")
        try:
            payload = {'token': code}
            response = requests.post(API_URL, data=payload, timeout=5)
            
            if response.status_code == 200:
                result = response.json()
                if result.get('success'):
                    user = result.get('user', {})
                    name = user.get('name', 'Unknown')
                    status = user.get('status', 'Unknown')
                    print(f"SUCCESS: {name} is now {status}")
                else:
                    print(f"DENIED: {result.get('message', 'Unknown error')}")
            else:
                print(f"SERVER ERROR: Status Code {response.status_code}")
        except Exception as e:
            print(f"CONNECTION FAILED: {e}")

    def run(self):
        with keyboard.Listener(on_press=self.on_press) as listener:
            listener.join()

if __name__ == "__main__":
    scanner = BackgroundScanner()
    try:
        scanner.run()
    except KeyboardInterrupt:
        print("\nExiting...")
        sys.exit(0)
