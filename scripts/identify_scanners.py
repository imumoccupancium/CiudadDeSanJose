import win32gui
import win32api
import win32con
import struct
import ctypes
from ctypes import wintypes

# Windows Raw Input Constants
WM_INPUT = 0x00FF
RIDEV_INPUTSINK = 0x00000100
RID_INPUT = 0x10000003
RIM_TYPEKEYBOARD = 1

class RAWINPUTHEADER(ctypes.Structure):
    _fields_ = [
        ("dwType", wintypes.DWORD),
        ("dwSize", wintypes.DWORD),
        ("hDevice", wintypes.HANDLE),
        ("wParam", wintypes.WPARAM),
    ]

class RAWKEYBOARD(ctypes.Structure):
    _fields_ = [
        ("MakeCode", wintypes.USHORT),
        ("Flags", wintypes.USHORT),
        ("Reserved", wintypes.USHORT),
        ("VKey", wintypes.USHORT),
        ("Message", wintypes.UINT),
        ("ExtraInformation", wintypes.ULONG),
    ]

class RAWINPUT(ctypes.Structure):
    class _U(ctypes.Union):
        _fields_ = [
            ("keyboard", RAWKEYBOARD),
        ]
    _fields_ = [
        ("header", RAWINPUTHEADER),
        ("data", _U),
    ]

def get_device_name(h_device):
    size = wintypes.UINT()
    ctypes.windll.user32.GetRawInputDeviceInfoW(h_device, 0x20000007, None, ctypes.byref(size))
    name_buffer = ctypes.create_unicode_buffer(size.value)
    ctypes.windll.user32.GetRawInputDeviceInfoW(h_device, 0x20000007, name_buffer, ctypes.byref(size))
    return name_buffer.value

def register_raw_input(hwnd):
    class RAWINPUTDEVICE(ctypes.Structure):
        _fields_ = [
            ("usUsagePage", wintypes.USHORT),
            ("usUsage", wintypes.USHORT),
            ("dwFlags", wintypes.DWORD),
            ("hwndTarget", wintypes.HWND),
        ]
    
    dev = RAWINPUTDEVICE()
    dev.usUsagePage = 0x01
    dev.usUsage = 0x06 # Keyboard
    dev.dwFlags = RIDEV_INPUTSINK
    dev.hwndTarget = hwnd
    
    if not ctypes.windll.user32.RegisterRawInputDevices(ctypes.byref(dev), 1, ctypes.sizeof(dev)):
        print("Failed to register raw input device")

def wnd_proc(hwnd, msg, wparam, lparam):
    if msg == WM_INPUT:
        size = wintypes.UINT()
        ctypes.windll.user32.GetRawInputData(lparam, RID_INPUT, None, ctypes.byref(size), ctypes.sizeof(RAWINPUTHEADER))
        
        raw = RAWINPUT()
        if ctypes.windll.user32.GetRawInputData(lparam, RID_INPUT, ctypes.byref(raw), ctypes.byref(size), ctypes.sizeof(RAWINPUTHEADER)):
            if raw.header.dwType == RIM_TYPEKEYBOARD:
                # Key down only (Flags == 0 or 2)
                if raw.data.keyboard.Message == win32con.WM_KEYDOWN:
                    h_device = raw.header.hDevice
                    name = get_device_name(h_device)
                    print(f"\n[KEY DETECTED]")
                    print(f"Device Handle: {h_device}")
                    print(f"Device Path: {name}")
                    print(f"--------------------------------------------------")
                    print("ACTION REQUIRED: If this was the INSIDE scanner, copy the 'Device Path' above.")
                    print("--------------------------------------------------")

    return win32gui.DefWindowProc(hwnd, msg, wparam, lparam)

def main():
    print("=== Ciudad De San Jose Scanner Identifier ===")
    print("This script will help identify your scanners based on their USB port address.")
    print("\nDIRECTIONS:")
    print("1. Scan any QR code using the 'INSIDE' (Entry) scanner.")
    print("2. Look at the 'Device Path' that appears.")
    print("3. Do the same for the 'OUTSIDE' (Exit) scanner.")
    print("\nPress Ctrl+C to stop.\n")

    wc = win32gui.WNDCLASS()
    wc.lpfnWndProc = wnd_proc
    wc.lpszClassName = "ScannerIdentifier"
    hInstance = win32api.GetModuleHandle(None)
    wc.hInstance = hInstance
    class_atom = win32gui.RegisterClass(wc)
    
    hwnd = win32gui.CreateWindow(class_atom, "Scanner Identifier", 0, 0, 0, 0, 0, 0, 0, hInstance, None)
    register_raw_input(hwnd)
    
    win32gui.PumpMessages()

if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        print("\nExiting...")
