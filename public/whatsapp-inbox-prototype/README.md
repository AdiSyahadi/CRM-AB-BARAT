# WhatsApp Inbox Prototype - Donatur CRM

Prototype UI/UX untuk fitur WhatsApp Inbox terintegrasi dengan Donatur CRM.

## 📁 File Structure

```
whatsapp-inbox-prototype/
├── index.html      # Main HTML file
├── styles.css      # Custom CSS (selaras dengan existing CRM)
├── app.js          # Alpine.js application logic
└── README.md       # This file
```

## 🎨 Design System

Prototype ini menggunakan design system yang **selaras dengan Donatur CRM** existing:

### Color Palette
- **Primary**: `#10B981` (Green) - Main brand color
- **Primary Variants**: 50-900 (dari light ke dark)
- **Segment Colors**:
  - VIP: `#FEF3C7` / `#92400E` (Yellow)
  - Loyal: `#D1FAE5` / `#065F46` (Green)
  - New: `#DBEAFE` / `#1E40AF` (Blue)
  - At Risk: `#FFEDD5` / `#9A3412` (Orange)
  - Churned: `#FEE2E2` / `#991B1B` (Red)

### Typography
- **Font**: Inter (Google Fonts)
- **Weights**: 300, 400, 500, 600, 700, 800

### Icons
- **Library**: Bootstrap Icons 1.11.3
- **CDN**: https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css

### Framework
- **CSS**: Tailwind CSS (via CDN)
- **JS**: Alpine.js 3.x (via CDN)

## 🚀 How to Preview

### Option 1: Direct File
1. Buka file `index.html` langsung di browser
2. Atau klik kanan → Open with → Browser pilihan Anda

### Option 2: Local Server (Recommended)
```bash
# Jika ada PHP
php -S localhost:8000

# Jika ada Python
python -m http.server 8000

# Jika ada Node.js
npx http-server -p 8000
```
Lalu buka: `http://localhost:8000/whatsapp-inbox-prototype/`

### Option 3: Via Laravel (dari root project)
```
http://localhost:8000/whatsapp-inbox-prototype/index.html
```

## ✨ Features yang Sudah Diimplementasi

### Left Panel (Inbox List)
- ✅ Search conversations
- ✅ Filter tabs (All, Unread, VIP, At Risk, New)
- ✅ Conversation list dengan:
  - Avatar dengan initial & segment color
  - Unread badge
  - Star indicator
  - Segment badge
  - Last message preview
  - Timestamp
- ✅ Stats footer (Total, Unread, VIP)

### Center Panel (Conversation)
- ✅ Header dengan donatur info & online status
- ✅ Message bubbles (incoming & outgoing)
- ✅ Read receipts (checkmarks)
- ✅ Typing indicator (animated dots)
- ✅ Message input dengan:
  - **Emoji Picker** (modal dengan search & kategori) ✨ BARU
  - **File Attachment** (preview & caption) ✨ BARU
  - **Template Selector** (modal dengan kategori) ✨ BARU
  - Send button
- ✅ Quick actions bar:
  - **Templates** (modal popup) ✨ BARU
  - **Quick Reply** (dropdown) ✨ BARU
  - Assign (UI only, needs backend)
- ✅ Empty state (ketika belum pilih chat)

### Right Panel (Donatur Info)
- ✅ Profile card dengan avatar & segment
- ✅ Contact information
- ✅ Donation metrics (Lifetime Value, Frequency, Average, Last)
- ✅ Engagement score dengan progress bar
- ✅ Quick actions cards:
  - View Full Profile
  - Add Note (needs backend)
  - Set Reminder (needs backend)
  - Log Donation (needs backend)

### Header & Navigation
- ✅ Session switcher (UI only, needs backend)
- ✅ Search global
- ✅ **Notification Dropdown** (dengan badge unread) ✨ BARU
- ✅ Tutorial link
- ✅ User menu
  - Log Donation
- ✅ Recent history timeline

### Top Header
- ✅ Session selector (switch WA session)
- ✅ Notification bell
- ✅ User profile

## 🎯 Interactive Features

### Already Working:
1. **Select Conversation**: Klik conversation di left panel → muncul di center
2. **Send Message**: Ketik di input → tekan Enter atau klik Send
3. **Toggle Star**: Klik star icon di header conversation
4. **Search**: Ketik di search box → filter real-time
5. **Filter Tabs**: Klik tab → filter berdasarkan segment
6. **Incoming Message Simulation**: Otomatis muncul setelah 5 detik
7. **Typing Indicator**: Muncul sebelum message masuk
8. **Read Receipt**: Checkmark berubah setelah 2 detik
9. **Emoji Picker**: Klik icon emoji, pilih emoji dari grid (100+ emoji) ✨ BARU
10. **Template Selector**: Klik button Templates, pilih dari 6 template kategori ✨ BARU
11. **Quick Reply**: Klik button Quick Reply, pilih dari 5 quick replies ✨ BARU
12. **File Attachment**: Klik icon paperclip, pilih file, tambah caption, kirim ✨ BARU
13. **Notifications**: Klik bell icon, lihat 3 notifikasi dengan badge unread ✨ BARU

## 📱 Responsive Design

- Desktop: 3 panel layout (Inbox | Conversation | Info)
- Tablet: 3 panel dengan width yang disesuaikan
- Mobile: Stack layout (satu panel tampil per waktu)

## 🎨 Animation & Transitions

- Smooth transitions untuk semua interaksi
- Bounce animation untuk typing indicator
- Fade in untuk new messages
- Hover effects pada buttons & cards
- Slide transitions untuk panels

## 🔧 Customization

### Mengganti Warna Primary
Edit di `index.html` bagian `tailwind.config`:
```javascript
colors: {
    primary: {
        500: '#10B981', // Ganti dengan warna pilihan
        // ... adjust variants
    }
}
```

### Menambah Conversation
Edit di `app.js` bagian `conversations` array:
```javascript
conversations: [
    {
        id: 9, // unique ID
        name: 'Nama Donatur',
        phone: '0812-XXXX-XXXX',
        initial: 'N', // Initial untuk avatar
        last_message: 'Pesan terakhir...',
        // ... dst
    }
]
```

### Menambah Message Template
(Akan ditambahkan di versi selanjutnya)

## 📝 Notes

### Dummy Data
- Saat ini menggunakan dummy data di `app.js`
- Conversations, messages, dan donatur info semua hardcoded
- Untuk production: ganti dengan API calls

### Tidak Termasuk (Needs Backend):
- ❌ Add Note modal (needs database)
- ❌ Set Reminder (needs database + cron)
- ❌ Log Donation (needs donatur integration)
- ❌ Assign CS (needs database)
- ❌ Session Switcher (needs backend session management)
- ❌ View Full Profile page (needs route)
- ❌ Broadcast feature
- ❌ Voice message
- ❌ Backend integration
- ❌ Real websocket connection
- ❌ Database persistence

### ✅ Fully Functional (Frontend Only) ✨ BARU:
- ✅ **Emoji Picker**: 100+ emoji dalam grid, bisa search
- ✅ **Template Selector**: 6 template dengan kategori (greeting, follow-up, info, etc)
- ✅ **Quick Reply**: 5 quick replies siap pakai
- ✅ **File Attachment**: Select file, preview dengan icon, tambah caption, kirim (simulated)
- ✅ **Notification Dropdown**: List notifikasi dengan badge unread count
- ✅ Search & filter conversations
- ✅ Send/receive messages (simulated)
- ✅ Star conversations
- ✅ Typing indicator

## 🐛 Known Issues / Limitations

1. **No Persistence**: Refresh page = data hilang (normal untuk prototype)
2. **No Real WhatsApp Connection**: Hanya simulasi UI
3. **Limited Messages**: Setiap conversation hanya punya beberapa message sample
4. **No Pagination**: Belum ada load more untuk conversation/message
5. **Simulated File Upload**: File attachment hanya simulasi (no real upload to server)

## 🔜 Next Steps (Setelah Approval UI)

1. Convert ke Blade components
2. Integrasi dengan DonaturCrmController
3. Setup routes & API endpoints
4. Connect ke WhatsApp API (dari dokumentasi yang ada)
5. Real-time dengan Laravel Echo + Pusher
6. Database schema implementation
7. Template management system
8. Broadcast feature
9. Media handling

## 💡 Tips untuk Review

1. **Coba semua interaksi**: Klik conversation, kirim message, toggle filter
2. **Perhatikan animasi**: Smooth transitions, typing indicator, etc
3. **Check responsive**: Resize browser untuk lihat behavior mobile
4. **Lihat detail**: Hover effects, color consistency, icon usage
5. **Test edge cases**: Empty state, long message, etc

## 📞 Support

Jika ada yang perlu disesuaikan:
- Warna tidak cocok
- Layout kurang pas
- Animasi terlalu lambat/cepat
- Icon perlu diganti
- dll

Silakan berikan feedback untuk revisi! 🚀

---

**Version**: 1.0.0 (Prototype)  
**Created**: January 2026  
**Status**: Ready for Review ✅
