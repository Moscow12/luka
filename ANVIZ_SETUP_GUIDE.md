# Anviz Device Setup Guide

## Overview
This system now supports both **ZKTeco** and **Anviz** fingerprint devices with different connection protocols.

## Quick Start

### 1. Add Your Anviz Device
1. Go to **HR Management** → **Attendance** → **Fingerprint Devices**
2. Click **"ADD DEVICE"**
3. Fill in the form:
   - **Device Name**: Give it a descriptive name (e.g., "Main Entrance - Anviz")
   - **Device Type**: Select **"Anviz"**
   - **IP Address**: Enter your device IP (e.g., 192.168.1.100)
   - **Username**: Default is `admin`
   - **Password**: Default is `admin`
   - **Location**: Optional (e.g., "Building A, Floor 1")
4. Click **"Register Device"**

### 2. Run Diagnostic (Important!)
1. Find your device in the list
2. Click the **"Diagnose"** button
3. Check the logs or contact support with the diagnostic output

The diagnostic will tell you:
- ✓ Which ports are open on your device
- ✓ Which protocol your device uses (HTTP API or TCP)
- ✓ Which API endpoints work
- ✓ Specific recommendations for your model

### 3. Test Connection
1. Click the **"Test"** button
2. For Anviz devices, this performs a ping test
3. If successful, status changes to "Active"

### 4. Sync Attendance
1. Click the **"Sync"** button
2. System attempts to fetch attendance records

---

## Troubleshooting

### Problem: "Could not retrieve data from Anviz device"

This means the HTTP API endpoints aren't responding. Run diagnostics first!

**Common Causes:**

#### A) Device Uses TCP Protocol (Not HTTP)
**Symptoms:** Diagnostic shows port 5010 or 5011 open, but no HTTP ports
**Solution:**
- Your device uses Anviz proprietary TCP protocol
- This requires additional setup:
  1. Install PyAnviz library: `pip3 install pyanviz`
  2. Contact developer to integrate TCP protocol support
  3. OR use Anviz CrossChex software to export data manually

#### B) Wrong Credentials
**Symptoms:** Diagnostic shows HTTP ports open but 401/403 errors
**Solution:**
- Try default credentials: `admin`/`admin`
- Check device manual for default password
- Access device web interface and verify/reset credentials

#### C) API Not Enabled
**Symptoms:** Diagnostic shows HTTP ports open but no endpoint responses
**Solution:**
1. Access device web interface: `http://[device-ip]`
2. Login with admin credentials
3. Navigate to **Settings** → **Network** → **API Access**
4. Enable HTTP API access
5. Save and reboot device

#### D) Firewall Blocking
**Symptoms:** Diagnostic shows all ports closed
**Solution:**
- Check server firewall: `sudo ufw status`
- Check device firewall settings in web interface
- Verify network routing between server and device

---

## Device Models & Protocols

### HTTP API Models (Supported)
These models typically work out-of-the-box:
- Anviz W1, W2 series
- Anviz VF30 series
- Anviz M5 series
- Any model with CrossChex Cloud support

### TCP Protocol Models (Needs Additional Setup)
These models require PyAnviz or SDK:
- Anviz T60 series
- Anviz OA1000 series
- Older Anviz models (pre-2015)

### How to Check Your Model
1. Look at device label/sticker
2. Access web interface - check **Device Info** page
3. Run diagnostic script - it will detect the protocol

---

## Manual Diagnostic (Command Line)

If you want to run diagnostic manually:

```bash
cd /home/moscow/Desktop/test/dasher/public
python3 anviz_diagnostic.py 192.168.1.100
```

Replace `192.168.1.100` with your device IP.

---

## Alternative: Using Anviz CrossChex Software

If the device doesn't support HTTP API:

1. Download **CrossChex Standard** from Anviz website
2. Install on Windows PC
3. Add your device in CrossChex
4. Export attendance data to CSV
5. Import CSV into this system (feature to be added)

---

## Common Ports Reference

| Port  | Protocol              | Usage                    |
|-------|-----------------------|--------------------------|
| 80    | HTTP                  | Web interface & API      |
| 443   | HTTPS                 | Secure web & API         |
| 5010  | Anviz TCP (Primary)   | Proprietary protocol     |
| 5011  | Anviz TCP (Alt)       | Alternative TCP port     |
| 8080  | HTTP Alternate        | Alternative web port     |
| 37777 | Anviz Legacy          | Old protocol             |

---

## Need Help?

1. **Run Diagnostic** first - it provides specific recommendations
2. **Check Logs**: Diagnostic output is saved to Laravel logs
3. **Device Manual**: Check your specific model documentation
4. **Contact Support**: Provide diagnostic output for faster help

---

## Developer Notes

### Files Related to Anviz Sync:
- `/public/anviz_sync_v2.py` - Main sync script (HTTP API)
- `/public/anviz_diagnostic.py` - Diagnostic tool
- `/app/Livewire/Hr/Attendance/Fpdevices.php` - Backend logic
- `/resources/views/livewire/hr/attendance/fpdevices.blade.php` - UI

### Adding TCP Protocol Support:
1. Install: `pip3 install pyanviz`
2. Create new script based on PyAnviz documentation
3. Update `syncAnvizDevice()` method to call TCP script
4. Test with actual TCP-based Anviz device

---

Last Updated: 2026-05-11
