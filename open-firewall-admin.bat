@echo off
echo ================================================
echo  TechMizane - Firewall Port 8000 Setup
echo ================================================
echo.

:: Add firewall rule for port 8000 (HTTP)
netsh advfirewall firewall add rule name="Docker TechMizane 8000" dir=in action=allow protocol=TCP localport=8000
echo [OK] Port 8000 opened

:: Add firewall rule for port 8443 (HTTPS)
netsh advfirewall firewall add rule name="Docker TechMizane 8443" dir=in action=allow protocol=TCP localport=8443
echo [OK] Port 8443 opened

echo.
echo ================================================
echo  Done! The app is now accessible at:
echo  http://192.168.192.38:8000/
echo ================================================
echo.
pause
