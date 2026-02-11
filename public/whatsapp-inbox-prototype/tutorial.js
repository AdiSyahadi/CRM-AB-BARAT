/**
 * Tutorial Page - Alpine.js App
 * Interactive tutorial and guide functionality
 */

function tutorialApp() {
    return {
        // ============================================
        // STATE MANAGEMENT
        // ============================================
        
        // Tutorial sections
        sections: [
            { id: 'overview', title: 'Overview Sistem' },
            { id: 'inbox', title: 'Mengelola Inbox' },
            { id: 'messages', title: 'Mengirim Pesan' },
            { id: 'donatur-info', title: 'Informasi Donatur' },
            { id: 'best-practices', title: 'Best Practices' },
            { id: 'faq', title: 'FAQ' }
        ],
        
        // Completed sections (stored in localStorage)
        completedSections: [],
        
        // Open FAQ index
        openFaq: null,
        
        // FAQ data
        faqs: [
            {
                question: 'Bagaimana cara memulai menggunakan WhatsApp CRM?',
                answer: 'Pertama, pastikan Anda sudah login. Kemudian pilih session WhatsApp yang aktif dari dropdown di header. Setelah itu, Anda bisa langsung melihat inbox dan mulai mengelola percakapan dengan donatur.'
            },
            {
                question: 'Apa perbedaan antara segment VIP, Loyal, dan New?',
                answer: 'VIP adalah donatur dengan total donasi ≥10jt atau ≥10 transaksi. Loyal adalah donatur yang melakukan ≥3 transaksi dalam 6 bulan terakhir. New adalah donatur yang baru registrasi kurang dari 30 hari yang lalu.'
            },
            {
                question: 'Bagaimana cara mengirim pesan menggunakan template?',
                answer: 'Klik icon petir (⚡) di sebelah input box untuk membuka template selector. Pilih template yang sesuai, lalu sistem akan otomatis mengisi input box dengan template tersebut. Anda bisa mengedit sebelum mengirim.'
            },
            {
                question: 'Apa itu Engagement Score dan bagaimana cara kerjanya?',
                answer: 'Engagement Score adalah nilai 0-100 yang mengukur seberapa engaged donatur tersebut. Dihitung berdasarkan Recency (kapan terakhir donasi), Frequency (seberapa sering), Monetary (total donasi), dan Tenure (lama jadi donatur). Score tinggi = donatur sangat aktif dan responsif.'
            },
            {
                question: 'Bagaimana cara menandai conversation sebagai penting?',
                answer: 'Klik icon bintang (⭐) di header conversation untuk toggle favorite. Conversation yang di-star akan muncul dengan indicator bintang di inbox list dan bisa difilter menggunakan tab "Starred".'
            },
            {
                question: 'Apakah pesan saya tersinkron dengan WhatsApp?',
                answer: 'Ya, semua pesan yang Anda kirim melalui CRM akan langsung terkirim ke WhatsApp donatur melalui API. Pesan masuk dari donatur juga akan otomatis muncul di inbox secara real-time melalui webhook.'
            },
            {
                question: 'Bagaimana cara membedakan pesan yang sudah dibaca?',
                answer: 'Pesan yang sudah dibaca ditandai dengan checkmark biru (✓✓). Pesan yang baru terkirim memiliki checkmark abu-abu (✓). Ini sama seperti di WhatsApp biasa.'
            },
            {
                question: 'Apa fungsi Quick Actions di panel kanan?',
                answer: 'Quick Actions memberikan akses cepat ke fungsi-fungsi penting seperti: View Full Profile (lihat detail lengkap), Add Note (tambah catatan), Set Reminder (jadwalkan follow-up), dan Log Donation (catat donasi baru). Semua tersedia dengan satu klik.'
            },
            {
                question: 'Bagaimana cara mencari conversation tertentu?',
                answer: 'Gunakan search box di atas inbox list. Anda bisa search berdasarkan nama donatur, nomor HP, atau bahkan isi pesan. Hasil akan muncul secara real-time saat Anda mengetik. Shortcut: Ctrl + K'
            },
            {
                question: 'Apakah data conversation aman dan private?',
                answer: 'Ya, semua data conversation tersimpan secara aman di database terenkripsi. Hanya CS yang memiliki akses yang bisa melihat percakapan. Semua aktivitas juga ter-log untuk audit trail.'
            },
            {
                question: 'Bagaimana cara switch antar session WhatsApp?',
                answer: 'Klik dropdown session di header (pojok kanan atas). Pilih session yang ingin Anda gunakan. Setiap session terhubung ke nomor WhatsApp yang berbeda, jadi pastikan pilih yang sesuai dengan tugas Anda.'
            },
            {
                question: 'Apa yang harus dilakukan jika donatur At Risk?',
                answer: 'Donatur At Risk adalah yang tidak donasi selama 60-90 hari. Prioritaskan untuk follow-up dengan mengirim pesan reminder yang friendly. Gunakan template "Follow-up Donasi" dan personalisasi dengan menyebut program terakhir mereka.'
            }
        ],
        
        // ============================================
        // COMPUTED PROPERTIES
        // ============================================
        
        get completionPercentage() {
            if (this.sections.length === 0) return 0;
            return Math.round((this.completedSections.length / this.sections.length) * 100);
        },
        
        // ============================================
        // INITIALIZATION
        // ============================================
        
        init() {
            console.log('Tutorial App initialized');
            
            // Load completed sections from localStorage
            this.loadProgress();
            
            // Setup scroll spy
            this.setupScrollSpy();
            
            // Add smooth scroll behavior
            document.documentElement.style.scrollBehavior = 'smooth';
        },
        
        // ============================================
        // PROGRESS TRACKING
        // ============================================
        
        loadProgress() {
            const saved = localStorage.getItem('tutorial_progress');
            if (saved) {
                try {
                    this.completedSections = JSON.parse(saved);
                } catch (e) {
                    this.completedSections = [];
                }
            }
        },
        
        saveProgress() {
            localStorage.setItem('tutorial_progress', JSON.stringify(this.completedSections));
        },
        
        toggleComplete(sectionId) {
            const index = this.completedSections.indexOf(sectionId);
            if (index > -1) {
                // Already completed, remove it
                this.completedSections.splice(index, 1);
            } else {
                // Mark as completed
                this.completedSections.push(sectionId);
            }
            this.saveProgress();
        },
        
        resetProgress() {
            if (confirm('Reset progress tutorial? Semua checkmark akan dihapus.')) {
                this.completedSections = [];
                this.saveProgress();
                
                // Show success message
                this.showToast('Progress berhasil direset! 🔄');
            }
        },
        
        // ============================================
        // NAVIGATION
        // ============================================
        
        scrollToSection(sectionId) {
            const element = document.getElementById(sectionId);
            if (element) {
                const offset = 100; // Header height + padding
                const elementPosition = element.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - offset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
                
                // Auto-mark as completed when scrolled to
                setTimeout(() => {
                    if (!this.completedSections.includes(sectionId)) {
                        this.completedSections.push(sectionId);
                        this.saveProgress();
                    }
                }, 1000);
            }
        },
        
        setupScrollSpy() {
            // Highlight active section in sidebar based on scroll position
            const observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const sectionId = entry.target.id;
                            // Could add active state to sidebar items here
                        }
                    });
                },
                {
                    threshold: 0.3,
                    rootMargin: '-100px 0px -50% 0px'
                }
            );
            
            // Observe all sections
            this.sections.forEach(section => {
                const element = document.getElementById(section.id);
                if (element) {
                    observer.observe(element);
                }
            });
        },
        
        // ============================================
        // FAQ METHODS
        // ============================================
        
        toggleFaq(index) {
            if (this.openFaq === index) {
                this.openFaq = null;
            } else {
                this.openFaq = index;
            }
        },
        
        // ============================================
        // UTILITY METHODS
        // ============================================
        
        showToast(message) {
            // Simple toast notification
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-4 right-4 bg-primary-600 text-white px-6 py-3 rounded-xl shadow-lg z-50 animate-fade-in';
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        },
        
        // ============================================
        // KEYBOARD SHORTCUTS
        // ============================================
        
        handleKeyboard(event) {
            // Ctrl + K: Focus search
            if (event.ctrlKey && event.key === 'k') {
                event.preventDefault();
                // Could focus search if implemented
                this.showToast('Shortcut: Ctrl + K untuk search');
            }
            
            // Ctrl + H: Go to home/overview
            if (event.ctrlKey && event.key === 'h') {
                event.preventDefault();
                this.scrollToSection('overview');
            }
            
            // Esc: Close FAQ
            if (event.key === 'Escape') {
                this.openFaq = null;
            }
        },
        
        // ============================================
        // PRINT FUNCTIONALITY
        // ============================================
        
        printTutorial() {
            window.print();
        },
        
        // ============================================
        // SHARE FUNCTIONALITY
        // ============================================
        
        shareTutorial() {
            if (navigator.share) {
                navigator.share({
                    title: 'WhatsApp CRM Tutorial',
                    text: 'Pelajari cara menggunakan WhatsApp CRM untuk mengelola donatur',
                    url: window.location.href
                }).catch(err => console.log('Error sharing:', err));
            } else {
                // Fallback: copy link
                navigator.clipboard.writeText(window.location.href);
                this.showToast('Link tutorial berhasil dicopy! 📋');
            }
        },
        
        // ============================================
        // SEARCH FUNCTIONALITY (Optional Enhancement)
        // ============================================
        
        searchTutorial(query) {
            // Could implement search across all tutorial content
            // This is a placeholder for future enhancement
            console.log('Searching for:', query);
        }
    }
}

// ============================================
// GLOBAL EVENT LISTENERS
// ============================================

// Listen for keyboard shortcuts globally
document.addEventListener('keydown', function(event) {
    // Get Alpine data
    const app = document.getElementById('tutorialApp');
    if (app && app.__x) {
        const data = app.__x.$data;
        if (data.handleKeyboard) {
            data.handleKeyboard(event);
        }
    }
});

// Smooth scroll polyfill for older browsers
if (!('scrollBehavior' in document.documentElement.style)) {
    // Add polyfill or fallback
    console.warn('Smooth scroll not supported, using fallback');
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

/**
 * Debounce function for search
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Format time ago
 */
function timeAgo(date) {
    const seconds = Math.floor((new Date() - date) / 1000);
    
    let interval = seconds / 31536000;
    if (interval > 1) return Math.floor(interval) + ' tahun lalu';
    
    interval = seconds / 2592000;
    if (interval > 1) return Math.floor(interval) + ' bulan lalu';
    
    interval = seconds / 86400;
    if (interval > 1) return Math.floor(interval) + ' hari lalu';
    
    interval = seconds / 3600;
    if (interval > 1) return Math.floor(interval) + ' jam lalu';
    
    interval = seconds / 60;
    if (interval > 1) return Math.floor(interval) + ' menit lalu';
    
    return Math.floor(seconds) + ' detik lalu';
}

/**
 * Check if element is in viewport
 */
function isInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

// ============================================
// ANALYTICS (Optional)
// ============================================

/**
 * Track tutorial events
 */
function trackTutorialEvent(eventName, data = {}) {
    // Placeholder for analytics
    console.log('Tutorial Event:', eventName, data);
    
    // Could integrate with Google Analytics, Mixpanel, etc.
    // Example:
    // gtag('event', eventName, data);
}

// Track page load
trackTutorialEvent('tutorial_page_loaded', {
    timestamp: new Date().toISOString()
});

// ============================================
// EXPORT (if using modules)
// ============================================

// Export for potential use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { tutorialApp, debounce, timeAgo, isInViewport };
}
