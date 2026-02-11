/**
 * WhatsApp Inbox - Alpine.js App
 * Main application logic
 */

function whatsappInboxApp() {
    console.log('🚀 WhatsApp Inbox App initialized');
    return {
        // ============================================
        // STATE MANAGEMENT
        // ============================================
        
        init() {
            console.log('✅ Alpine.js component mounted');
            console.log('📊 Initial state:', {
                showTemplateSelector: this.showTemplateSelector,
                showAssignCS: this.showAssignCS,
                showEmojiPicker: this.showEmojiPicker
            });
        },
        
        // Active session
        activeSession: {
            id: 1,
            name: 'CS Budi',
            phone: '0821-XXXX-1111',
            status: 'connected'
        },
        
        // Search & Filters
        searchQuery: '',
        activeFilter: 'all',
        
        // Frontend-only features state
        showEmojiPicker: false,
        showTemplateSelector: false,
        showAttachmentPreview: false,
        showQuickReply: false,
        showNotifications: false,
        showAssignCS: false,
        showAddNote: false,
        showSetReminder: false,
        showLogDonation: false,
        showFullProfile: false,
        
        emojiSearch: '',
        templateSearch: '',
        selectedFile: null,
        attachmentCaption: '',
        
        // Quick Actions data
        newNote: '',
        reminderDate: '',
        reminderTime: '',
        reminderNote: '',
        donationAmount: '',
        donationProgram: '',
        donationDate: '',
        
        // Emoji list
        emojis: ['😀', '😃', '😄', '😁', '😆', '😅', '🤣', '😂', '🙂', '🙃', '😉', '😊', '😇', '🥰', '😍', '🤩', '😘', '😗', '😚', '😙', '🥲', '😋', '😛', '😜', '🤪', '😝', '🤑', '🤗', '🤭', '🤫', '🤔', '🤐', '🤨', '😐', '😑', '😶', '😏', '😒', '🙄', '😬', '🤥', '😌', '😔', '😪', '🤤', '😴', '😷', '🤒', '🤕', '🤢', '🤮', '🤧', '🥵', '🥶', '😶‍🌫️', '🥴', '😵', '🤯', '🤠', '🥳', '🥸', '😎', '🤓', '🧐', '😕', '😟', '🙁', '☹️', '😮', '😯', '😲', '😳', '🥺', '😦', '😧', '😨', '😰', '😥', '😢', '😭', '😱', '😖', '😣', '😞', '😓', '😩', '😫', '🥱', '👍', '👎', '👏', '🙏', '💪', '✨', '🎉', '🎊', '❤️', '💕', '💖', '💗', '💙', '💚', '💛', '🧡', '💜', '🖤', '🤍', '🤎', '💔', '❣️', '💘', '💝', '🔥', '⭐', '✅', '❌'],
        
        // Message templates
        messageTemplates: [
            {
                id: 1,
                name: 'Salam Pembuka',
                category: 'greeting',
                message: 'Assalamualaikum warahmatullahi wabarakatuh, Bapak/Ibu. Perkenalkan saya {nama_cs} dari Yayasan. Ada yang bisa saya bantu?'
            },
            {
                id: 2,
                name: 'Terima Kasih Donasi',
                category: 'gratitude',
                message: 'Jazakallahu khairan katsiran atas donasi yang telah Bapak/Ibu berikan sebesar Rp {jumlah}. Semoga menjadi amal jariyah yang berkah.'
            },
            {
                id: 3,
                name: 'Follow Up Donatur',
                category: 'follow-up',
                message: 'Bapak/Ibu {nama_donatur}, sudah lama tidak berdonasi. Apakah ada kendala? Kami siap membantu.'
            },
            {
                id: 4,
                name: 'Konfirmasi Transfer',
                category: 'follow-up',
                message: 'Kami telah menerima donasi Bapak/Ibu sebesar Rp {jumlah}. Mohon konfirmasi jika ada perbedaan data.'
            },
            {
                id: 5,
                name: 'Info Program Baru',
                category: 'info',
                message: 'Kabar gembira! Kami memiliki program baru: {nama_program}. Informasi lebih lanjut bisa dilihat di link berikut.'
            },
            {
                id: 6,
                name: 'Penutup Percakapan',
                category: 'closing',
                message: 'Terima kasih atas waktu Bapak/Ibu. Jika ada yang ingin ditanyakan, jangan ragu untuk menghubungi kami kembali. Wassalamualaikum.'
            }
        ],
        
        // Quick replies
        quickReplies: [
            { id: 1, text: 'Baik, terima kasih' },
            { id: 2, text: 'Sedang kami proses' },
            { id: 3, text: 'Mohon tunggu sebentar' },
            { id: 4, text: 'Siap, akan kami bantu' },
            { id: 5, text: 'Terima kasih konfirmasinya' }
        ],
        
        // CS Team list for assignment
        csTeam: [
            { id: 1, name: 'CS Budi', status: 'online', activeChats: 12 },
            { id: 2, name: 'CS Ani', status: 'online', activeChats: 8 },
            { id: 3, name: 'CS Dedi', status: 'busy', activeChats: 15 },
            { id: 4, name: 'CS Siti', status: 'online', activeChats: 5 },
            { id: 5, name: 'CS Rina', status: 'offline', activeChats: 0 }
        ],
        
        // Notifications
        notifications: [
            {
                id: 1,
                title: 'Pesan Baru',
                message: 'Ahmad Ibrahim mengirim pesan baru',
                time: '2 menit lalu',
                icon: 'bi-chat-dots text-green-500',
                read: false
            },
            {
                id: 2,
                title: 'Donasi Masuk',
                message: 'Siti Fatimah berdonasi Rp 500.000',
                time: '15 menit lalu',
                icon: 'bi-cash-coin text-yellow-500',
                read: false
            },
            {
                id: 3,
                title: 'Reminder',
                message: 'Follow up dengan Muhammad Ali',
                time: '1 jam lalu',
                icon: 'bi-bell text-blue-500',
                read: true
            }
        ],
        
        // Filter tabs configuration
        filterTabs: [
            { key: 'all', label: 'All', icon: 'bi-chat-dots', count: 45 },
            { key: 'unread', label: 'Unread', icon: 'bi-circle-fill', count: 12 },
            { key: 'vip', label: 'VIP', icon: 'bi-star-fill', count: 8 },
            { key: 'at_risk', label: 'At Risk', icon: 'bi-exclamation-circle', count: 5 },
            { key: 'new', label: 'New', icon: 'bi-person-plus', count: 3 }
        ],
        
        // Conversations data
        conversations: [
            {
                id: 1,
                name: 'Ahmad Ibrahim',
                phone: '0812-3456-7890',
                initial: 'A',
                last_message: 'Alhamdulillah baik kak, terima kasih',
                time: '2 min',
                unread_count: 3,
                is_starred: true,
                is_replied: true,
                segment: 'vip',
                segment_label: 'VIP',
                did: 'D001234'
            },
            {
                id: 2,
                name: 'Siti Nurhaliza',
                phone: '0813-4567-8901',
                initial: 'S',
                last_message: 'Ok siap Pak, nanti saya transfer',
                time: '5 min',
                unread_count: 1,
                is_starred: false,
                is_replied: true,
                segment: 'loyal',
                segment_label: 'Loyal',
                did: 'D001235'
            },
            {
                id: 3,
                name: 'Rahma Dewi',
                phone: '0814-5678-9012',
                initial: 'R',
                last_message: 'Terima kasih infonya',
                time: '1 hour',
                unread_count: 0,
                is_starred: false,
                is_replied: false,
                segment: 'new',
                segment_label: 'New',
                did: 'D001236'
            },
            {
                id: 4,
                name: 'Yusuf Abdullah',
                phone: '0815-6789-0123',
                initial: 'Y',
                last_message: 'Assalamualaikum, ada program apa saja?',
                time: '3 hours',
                unread_count: 2,
                is_starred: true,
                is_replied: false,
                segment: 'vip',
                segment_label: 'VIP',
                did: 'D001237'
            },
            {
                id: 5,
                name: 'Fatimah Zahra',
                phone: '0816-7890-1234',
                initial: 'F',
                last_message: 'Insya Allah nanti saya hubungi lagi',
                time: '5 hours',
                unread_count: 0,
                is_starred: false,
                is_replied: true,
                segment: 'at_risk',
                segment_label: 'At Risk',
                did: 'D001238'
            },
            {
                id: 6,
                name: 'Muhammad Ridho',
                phone: '0817-8901-2345',
                initial: 'M',
                last_message: 'Baik Bu, saya tertarik dengan program wakaf',
                time: '1 day',
                unread_count: 0,
                is_starred: false,
                is_replied: false,
                segment: 'loyal',
                segment_label: 'Loyal',
                did: 'D001239'
            },
            {
                id: 7,
                name: 'Khadijah Ali',
                phone: '0818-9012-3456',
                initial: 'K',
                last_message: 'Sudah saya transfer ya',
                time: '2 days',
                unread_count: 0,
                is_starred: false,
                is_replied: true,
                segment: 'vip',
                segment_label: 'VIP',
                did: 'D001240'
            },
            {
                id: 8,
                name: 'Abdullah Hakim',
                phone: '0819-0123-4567',
                initial: 'A',
                last_message: 'Mohon info lebih lanjut',
                time: '2 days',
                unread_count: 1,
                is_starred: false,
                is_replied: false,
                segment: 'new',
                segment_label: 'New',
                did: 'D001241'
            }
        ],
        
        // Selected conversation
        selectedConversation: null,
        
        // Messages for selected conversation
        messages: [],
        
        // Message input
        messageInput: '',
        
        // Typing indicator
        isTyping: false,
        
        // Show templates
        showTemplates: false,
        
        // Donatur info for right panel
        donaturInfo: null,
        
        // Stats
        stats: {
            total: 45,
            unread: 12,
            vip: 8
        },
        
        // ============================================
        // COMPUTED PROPERTIES
        // ============================================
        
        get filteredConversations() {
            let filtered = this.conversations;
            
            // Apply search filter
            if (this.searchQuery.trim()) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(conv => 
                    conv.name.toLowerCase().includes(query) ||
                    conv.phone.includes(query) ||
                    conv.last_message.toLowerCase().includes(query)
                );
            }
            
            // Apply tab filter
            switch (this.activeFilter) {
                case 'unread':
                    filtered = filtered.filter(c => c.unread_count > 0);
                    break;
                case 'vip':
                    filtered = filtered.filter(c => c.segment === 'vip');
                    break;
                case 'at_risk':
                    filtered = filtered.filter(c => c.segment === 'at_risk');
                    break;
                case 'new':
                    filtered = filtered.filter(c => c.segment === 'new');
                    break;
            }
            
            return filtered;
        },
        
        // ============================================
        // INITIALIZATION
        // ============================================
        
        init() {
            console.log('WhatsApp Inbox initialized');
            
            // Simulate auto-select first conversation after 500ms
            setTimeout(() => {
                if (this.conversations.length > 0) {
                    this.selectConversation(this.conversations[0]);
                }
            }, 500);
            
            // Simulate new message coming in
            setTimeout(() => {
                this.simulateIncomingMessage();
            }, 5000);
        },
        
        // ============================================
        // CONVERSATION METHODS
        // ============================================
        
        selectConversation(conversation) {
            this.selectedConversation = conversation;
            
            // Mark as read
            conversation.unread_count = 0;
            
            // Load messages for this conversation
            this.loadMessages(conversation.id);
            
            // Load donatur info
            this.loadDonaturInfo(conversation.id);
            
            // Scroll to bottom
            this.$nextTick(() => {
                this.scrollToBottom();
            });
        },
        
        loadMessages(conversationId) {
            // Simulate loading messages from API
            // In real app, this would be an API call
            
            const messageSets = {
                1: [ // Ahmad
                    {
                        id: 1,
                        from_me: false,
                        sender_name: 'Ahmad',
                        content: 'Assalamualaikum Pak, apa kabar?',
                        time: '10:15',
                        read: true
                    },
                    {
                        id: 2,
                        from_me: true,
                        sender_name: 'Budi',
                        content: 'Waalaikumsalam Pak Ahmad, Alhamdulillah baik. Bagaimana kabar Bapak?',
                        time: '10:20',
                        read: true
                    },
                    {
                        id: 3,
                        from_me: false,
                        sender_name: 'Ahmad',
                        content: 'Alhamdulillah baik kak, terima kasih',
                        time: '10:30',
                        read: true
                    },
                    {
                        id: 4,
                        from_me: true,
                        sender_name: 'Budi',
                        content: 'Ada program baru nih Pak, program wakaf produktif. Cocok untuk Bapak yang peduli pendidikan.',
                        time: '10:35',
                        read: false
                    }
                ],
                2: [ // Siti
                    {
                        id: 1,
                        from_me: true,
                        sender_name: 'Budi',
                        content: 'Selamat pagi Bu Siti, ada info program menarik nih',
                        time: '09:00',
                        read: true
                    },
                    {
                        id: 2,
                        from_me: false,
                        sender_name: 'Siti',
                        content: 'Ok siap Pak, nanti saya transfer',
                        time: '09:15',
                        read: true
                    }
                ]
            };
            
            this.messages = messageSets[conversationId] || [];
        },
        
        loadDonaturInfo(conversationId) {
            // Simulate loading donatur info
            const donaturData = {
                1: {
                    name: 'Ahmad Ibrahim',
                    did: 'D001234',
                    phone: '0812-3456-7890',
                    email: 'ahmad@email.com',
                    cs: 'Budi',
                    lifetime_value: 'Rp 15.5jt',
                    frequency: '12x',
                    avg_donation: 'Rp 1.2jt',
                    last_donation: '3 hari lalu',
                    engagement_score: 85
                },
                2: {
                    name: 'Siti Nurhaliza',
                    did: 'D001235',
                    phone: '0813-4567-8901',
                    email: 'siti@email.com',
                    cs: 'Budi',
                    lifetime_value: 'Rp 8.3jt',
                    frequency: '8x',
                    avg_donation: 'Rp 1jt',
                    last_donation: '1 minggu lalu',
                    engagement_score: 75
                }
            };
            
            this.donaturInfo = donaturData[conversationId] || {
                name: this.selectedConversation.name,
                did: this.selectedConversation.did,
                phone: this.selectedConversation.phone,
                email: 'N/A',
                cs: 'Budi',
                lifetime_value: 'Rp 0',
                frequency: '0x',
                avg_donation: 'Rp 0',
                last_donation: 'Belum donasi',
                engagement_score: 0
            };
        },
        
        toggleStar() {
            if (this.selectedConversation) {
                this.selectedConversation.is_starred = !this.selectedConversation.is_starred;
            }
        },
        
        // ============================================
        // MESSAGE METHODS
        // ============================================
        
        sendMessage() {
            if (!this.messageInput.trim() || !this.selectedConversation) return;
            
            // Create new message
            const newMessage = {
                id: this.messages.length + 1,
                from_me: true,
                sender_name: 'Budi',
                content: this.messageInput.trim(),
                time: this.getCurrentTime(),
                read: false
            };
            
            // Add to messages
            this.messages.push(newMessage);
            
            // Update conversation last message
            this.selectedConversation.last_message = this.messageInput.trim();
            this.selectedConversation.time = 'Just now';
            
            // Clear input
            this.messageInput = '';
            
            // Scroll to bottom
            this.$nextTick(() => {
                this.scrollToBottom();
            });
            
            // Simulate read receipt after 2s
            setTimeout(() => {
                newMessage.read = true;
            }, 2000);
            
            // In real app: Send via API to WhatsApp
            console.log('Message sent:', newMessage.content);
        },
        
        simulateIncomingMessage() {
            // Find a conversation
            const conv = this.conversations.find(c => c.id === 2);
            if (!conv) return;
            
            // Simulate typing
            this.isTyping = true;
            
            setTimeout(() => {
                this.isTyping = false;
                
                // Add new message
                const newMessage = {
                    id: this.messages.length + 1,
                    from_me: false,
                    sender_name: conv.name,
                    content: 'Pak, untuk program wakaf itu minimal berapa ya?',
                    time: this.getCurrentTime(),
                    read: false
                };
                
                // Update conversation
                conv.last_message = newMessage.content;
                conv.time = 'Just now';
                conv.unread_count++;
                
                // If this conversation is selected, add to messages
                if (this.selectedConversation?.id === conv.id) {
                    this.messages.push(newMessage);
                    this.$nextTick(() => {
                        this.scrollToBottom();
                    });
                    
                    // Auto mark as read after 1s
                    setTimeout(() => {
                        conv.unread_count = 0;
                    }, 1000);
                }
                
                // Update stats
                this.stats.unread++;
                
                // Play notification sound (optional)
                console.log('New message from:', conv.name);
            }, 3000);
        },
        
        // ============================================
        // UTILITY METHODS
        // ============================================
        
        getCurrentTime() {
            const now = new Date();
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            return `${hours}:${minutes}`;
        },
        
        scrollToBottom() {
            const container = document.getElementById('messagesContainer');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        },
        
        formatTime(timestamp) {
            // Simple time formatting
            return timestamp;
        },
        
        // ============================================
        // TEMPLATE METHODS
        // ============================================
        
        useTemplate(template) {
            this.messageInput = template.content;
            this.showTemplates = false;
        },
        
        // ============================================
        // SEARCH & FILTER
        // ============================================
        
        updateSearch(query) {
            this.searchQuery = query;
        },
        
        setFilter(filter) {
            this.activeFilter = filter;
        },
        
        // ============================================
        // EMOJI PICKER
        // ============================================
        
        get filteredEmojis() {
            if (!this.emojiSearch) return this.emojis;
            // Simple emoji search (in real app, you'd have emoji metadata)
            return this.emojis;
        },
        
        insertEmoji(emoji) {
            this.messageInput += emoji;
            this.showEmojiPicker = false;
            // Focus back to input
            setTimeout(() => {
                const input = document.querySelector('textarea[x-model="messageInput"]');
                if (input) input.focus();
            }, 100);
        },
        
        // ============================================
        // TEMPLATE SELECTOR
        // ============================================
        
        get filteredTemplates() {
            if (!this.templateSearch) return this.messageTemplates;
            const search = this.templateSearch.toLowerCase();
            return this.messageTemplates.filter(t => 
                t.name.toLowerCase().includes(search) || 
                t.message.toLowerCase().includes(search)
            );
        },
        
        useTemplate(template) {
            this.messageInput = template.message;
            this.showTemplateSelector = false;
            // Focus to input
            setTimeout(() => {
                const input = document.querySelector('textarea[x-model="messageInput"]');
                if (input) input.focus();
            }, 100);
        },
        
        // ============================================
        // FILE ATTACHMENT
        // ============================================
        
        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.selectedFile = file;
                this.attachmentCaption = '';
                this.showAttachmentPreview = true;
            }
        },
        
        getFileIcon(fileType) {
            if (fileType.startsWith('image/')) return 'bi-file-earmark-image text-blue-500';
            if (fileType.startsWith('video/')) return 'bi-file-earmark-play text-purple-500';
            if (fileType.startsWith('audio/')) return 'bi-file-earmark-music text-green-500';
            if (fileType.includes('pdf')) return 'bi-file-earmark-pdf text-red-500';
            if (fileType.includes('word')) return 'bi-file-earmark-word text-blue-600';
            if (fileType.includes('excel') || fileType.includes('spreadsheet')) return 'bi-file-earmark-excel text-green-600';
            return 'bi-file-earmark text-gray-500';
        },
        
        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        },
        
        sendAttachment() {
            if (!this.selectedFile || !this.selectedConversation) return;
            
            // Create attachment message
            const message = {
                id: Date.now(),
                type: 'outgoing',
                text: this.attachmentCaption || `📎 ${this.selectedFile.name}`,
                time: this.getCurrentTime(),
                status: 'sent',
                isAttachment: true,
                fileName: this.selectedFile.name,
                fileSize: this.formatFileSize(this.selectedFile.size),
                fileType: this.selectedFile.type
            };
            
            // Add to conversation
            const conversation = this.conversations.find(c => c.id === this.selectedConversation);
            if (conversation && conversation.messages) {
                conversation.messages.push(message);
                conversation.last_message = message.text;
                conversation.time = 'Baru saja';
            }
            
            // Reset
            this.selectedFile = null;
            this.attachmentCaption = '';
            this.showAttachmentPreview = false;
            
            // Reset file input
            document.getElementById('fileInput').value = '';
            
            // Scroll to bottom
            this.$nextTick(() => this.scrollToBottom());
        },
        
        cancelAttachment() {
            this.selectedFile = null;
            this.attachmentCaption = '';
            this.showAttachmentPreview = false;
            document.getElementById('fileInput').value = '';
        },
        
        // ============================================
        // QUICK REPLY
        // ============================================
        
        useQuickReply(reply) {
            this.messageInput = reply.text;
            this.showQuickReply = false;
            // Focus to input
            setTimeout(() => {
                const input = document.querySelector('textarea[x-model="messageInput"]');
                if (input) input.focus();
            }, 100);
        },
        
        // ============================================
        // NOTIFICATIONS
        // ============================================
        
        get unreadNotifications() {
            return this.notifications.filter(n => !n.read).length;
        },
        
        markAllAsRead() {
            this.notifications.forEach(n => n.read = true);
        },
        
        handleNotificationClick(notif) {
            notif.read = true;
            this.showNotifications = false;
            // In real app, navigate to relevant conversation or page
        },
        
        // ============================================
        // ASSIGN CS
        // ============================================
        
        assignToCS(cs) {
            if (!this.selectedConversation) return;
            
            // Simulate assignment
            const conversation = this.conversations.find(c => c.id === this.selectedConversation);
            if (conversation) {
                conversation.assignedTo = cs.name;
            }
            
            this.showAssignCS = false;
            
            // Show notification (simulated)
            alert(`Percakapan berhasil di-assign ke ${cs.name}`);
        },
        
        // ============================================
        // QUICK ACTIONS (RIGHT PANEL)
        // ============================================
        
        saveNote() {
            if (!this.newNote.trim()) return;
            
            // Simulate saving note
            console.log('Note saved:', this.newNote);
            alert(`Catatan berhasil disimpan!\n\n${this.newNote}`);
            
            this.newNote = '';
            this.showAddNote = false;
        },
        
        saveReminder() {
            if (!this.reminderDate || !this.reminderTime) {
                alert('Mohon isi tanggal dan waktu reminder');
                return;
            }
            
            // Simulate saving reminder
            const reminderText = `${this.reminderDate} ${this.reminderTime}\n${this.reminderNote || 'Follow up donatur'}`;
            console.log('Reminder saved:', reminderText);
            alert(`Reminder berhasil dijadwalkan!\n\n${reminderText}`);
            
            this.reminderDate = '';
            this.reminderTime = '';
            this.reminderNote = '';
            this.showSetReminder = false;
        },
        
        saveDonation() {
            if (!this.donationAmount || !this.donationProgram) {
                alert('Mohon isi jumlah donasi dan program');
                return;
            }
            
            // Simulate logging donation
            const donationText = `Rp ${parseInt(this.donationAmount).toLocaleString('id-ID')}\nProgram: ${this.donationProgram}\nTanggal: ${this.donationDate || new Date().toLocaleDateString('id-ID')}`;
            console.log('Donation logged:', donationText);
            alert(`Donasi berhasil dicatat!\n\n${donationText}`);
            
            this.donationAmount = '';
            this.donationProgram = '';
            this.donationDate = '';
            this.showLogDonation = false;
        }
    }
}
