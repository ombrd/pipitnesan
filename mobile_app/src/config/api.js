/**
 * Konfigurasi endpoint API backend Pipitnesan.
 *
 * Seluruh URL backend dikelola di file ini saja (tidak lagi di-hardcode di services/api.js).
 * Ubah nilai API_BASE_URL sesuai skenario development Anda:
 * - Emulator Android + backend lokal (Sail) : 'http://10.0.2.2/api'
 * - Perangkat fisik + backend lokal (LAN)   : 'http://<IP-komputer-anda>/api'
 * - Tunnel publik Ngrok (make share)        : 'https://<subdomain-anda>.ngrok-free.app/api'
 */
export const API_BASE_URL = 'https://karol-uninfringed-gaye.ngrok-free.dev/api';
