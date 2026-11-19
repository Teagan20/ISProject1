# ISProject1: CampusDesk (PHP + MySQL on Azure)

A student IT-support ticket portal. Three sites from one codebase:
| Host | Folder | Purpose |
|---|---|---|
| yourdomain.com / www | public/ | Student site (register, login, tickets) |
| admin.yourdomain.com | admin/ | Admin panel: tickets, stats, audit log |
| api.yourdomain.com | api/ | JSON ticket-stats endpoint |

## Security features (for the report)
- Passwords hashed with `password_hash()` (salted bcrypt), verified with `password_verify()`
- Prepared statements everywhere (SQL-injection safe); output escaped with `htmlspecialchars` (XSS safe)
- HTML5 validation (required, pattern, type, min/maxlength) AND server-side validation
- CSRF tokens, session regeneration, HttpOnly/Secure/SameSite cookies
- Brute-force lockout (5 failed logins / 15 min), audit log, Fail2ban on SSH
- Security headers (HSTS, CSP, X-Frame-Options, etc.), HTTP to HTTPS redirect

## Azure deployment
1. **Portal > Virtual machines > Create**: Ubuntu 22.04 LTS, size B1s/B2s, authentication = SSH key,
   inbound ports 22, 80, 443. Note the **public IP**. (Networking tab > set the IP to **Static**.)
2. SSH in: `ssh azureuser@PUBLIC_IP`
3. Upload the project: `scp -r ISProject1 azureuser@PUBLIC_IP:~/` then
   `cd ~/ISProject1/deploy && sudo bash setup.sh`
4. Create an admin: `php ~/ISProject1/deploy/create_admin.php admin@yourdomain.com 'StrongPass123'`
5. Change the DB password (schema.sql, then `DB_PASS` in inc/bootstrap.php).
6. Test over HTTP first: `http://PUBLIC_IP` (screenshot).

## DNS (Azure DNS or your registrar)
Buy a domain (Namecheap, Porkbun, KENIC registrar). In **Azure Portal > DNS zones > Create** the zone,
then point the registrar's nameservers to the 4 Azure NS values. Add records:
| Type | Name | Value |
|---|---|---|
| A | @ | VM public IP |
| A | www | VM public IP |
| A | admin | VM public IP |
| A | api | VM public IP |
| CNAME | www (alternative) | yourdomain.com |
| MX | @ | mail host (optional) |
| TXT | @ | `v=spf1 -all` (or SPF for your mail) |
| NS | @ | Azure nameservers (auto-created) |

Verify: `nslookup yourdomain.com`, `dig admin.yourdomain.com`, `dig yourdomain.com NS`

## ZeroSSL
1. zerossl.com > New Certificate > domains: `yourdomain.com www.yourdomain.com admin.yourdomain.com api.yourdomain.com`
   (a wildcard needs a paid plan; a multi-domain list is fine).
2. Validate by **CNAME in DNS** (simplest), or HTTP file: put the file in `/var/www/validation/` (path
   `/.well-known/pki-validation/` is pre-configured in the vhost).
3. Download and copy `certificate.crt`, `ca_bundle.crt`, `private.key` to `/etc/ssl/isproject1/`
   (`sudo chmod 600 private.key`).
4. Edit domain names in `/etc/apache2/sites-available/isproject1.conf`, then:
   `sudo a2ensite isproject1.conf && sudo apache2ctl configtest && sudo systemctl reload apache2`
5. Browse `https://yourdomain.com`. Screenshot the padlock/certificate. Test on ssllabs.com and securityheaders.com.

## Screenshot checklist
VM overview + public IP; DNS zone records; nslookup/dig output; each subdomain; padlock + certificate;
hashed passwords in `SELECT email,password_hash FROM users;`; validation error messages; lockout message;
audit log; admin panel; API JSON.

## Team roles (fill in)
| Member | Role |
|---|---|
| A | Azure VM and networking |
| B | DNS and subdomains |
| C | SSL/ZeroSSL and Apache |
| D | App code, validation, hashing |
| E | Report, demo video |
Use one GitHub repo so each member's commits are visible.
