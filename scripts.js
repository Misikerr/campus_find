// Modal open/close
const modal = document.getElementById('reportModal');
const closeModal = document.getElementById('closeModal');

function openModal() {
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function hideModal() {
    modal.style.display = 'none';
    document.body.style.overflow = '';
}

document.querySelectorAll('.report-btn').forEach(btn => btn.addEventListener('click', openModal));
closeModal.addEventListener('click', hideModal);

// Toggle Lost/Found
const lostBtn = document.getElementById('lostBtn');
const foundBtn = document.getElementById('foundBtn');
const dateLabel = document.getElementById('dateLabel');
let currentType = 'lost'; // Default

function setLostActive() {
    lostBtn.classList.add('active', 'lost');
    foundBtn.classList.remove('active', 'found');
    dateLabel.textContent = 'Date Lost';
    currentType = 'lost';
}

function setFoundActive() {
    foundBtn.classList.add('active', 'found');
    lostBtn.classList.remove('active', 'lost');
    dateLabel.textContent = 'Date Found';
    currentType = 'found';
}

lostBtn.onclick = (e) => { e.preventDefault(); setLostActive(); };
foundBtn.onclick = (e) => { e.preventDefault(); setFoundActive(); };

// Default to Found tab if opened from main green button
document.querySelectorAll('.report-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        if (btn.classList.contains('main')) {
            setFoundActive();
        } else {
            setLostActive();
        }
    });
});

// Category Icons Mapping
const categoryIcons = {
    'Other': '📦',
    'Electronics': '📱',
    'Documents': '📄',
    'Accessories': '👓',
    'Bags': '🎒',
    'Books': '📚'
};

// Fetch and Render Items
let allItems = [];
let announcementsData = [];
// Same-origin relative API base; works in local subfolder and on server root
const API_BASE_URL = 'php';

function resolveImageUrl(rawPath) {
    if (!rawPath) return '';
    if (/^https?:\/\//i.test(rawPath)) return rawPath;
    return `${API_BASE_URL}/${rawPath}`;
}

function formatDateDisplay(input) {
    const d = new Date(input);
    return Number.isNaN(d.getTime()) ? '' : d.toLocaleDateString();
}

// Search/filter controls
const searchInput = document.getElementById('searchInput');
const typeFilter = document.getElementById('typeFilter');
const categoryFilter = document.getElementById('categoryFilter');
const locationFilter = document.getElementById('locationFilter');

async function fetchItems() {
    try {
        const response = await fetch(`${API_BASE_URL}/items.php`);
        const raw = await response.text();
        let data;
        try { data = JSON.parse(raw); } catch (e) { throw new Error(`items.php non-JSON response: ${raw}`); }
        if (!response.ok) throw new Error(data?.message || raw || 'items.php request failed');
        // Normalize booleans that may arrive as strings
        allItems = data.map(d => ({
            ...d,
            resolved: d.resolved === true || d.resolved === 1 || d.resolved === '1' || d.resolved === 'true',
            owner_met: d.owner_met === true || d.owner_met === 1 || d.owner_met === '1' || d.owner_met === 'true'
        }));
        const ownerMetItems = allItems.filter(i => i.owner_met);
        renderOwnerMet(ownerMetItems);
        populateFilters(allItems);
        applyFilters();
    } catch (error) {
        console.error('Error fetching items:', error);
        alert('Failed to load items: ' + error.message);
    }
}

function populateFilters(items) {
    if (categoryFilter) {
        const cats = Array.from(new Set(items.map(i => i.category).filter(Boolean))).sort();
        categoryFilter.innerHTML = '<option value="all">All Categories</option>' + cats.map(c => `<option value="${c}">${c}</option>`).join('');
    }
    if (locationFilter) {
        const locs = Array.from(new Set(items.map(i => i.location).filter(Boolean))).sort();
        locationFilter.innerHTML = '<option value="all">All Locations</option>' + locs.map(l => `<option value="${l}">${l}</option>`).join('');
    }
}

function applyFilters() {
    const query = (searchInput?.value || '').toLowerCase().trim();
    const type = typeFilter?.value || 'all';
    const cat = categoryFilter?.value || 'all';
    const loc = locationFilter?.value || 'all';

    let active = allItems.filter(i => !i.resolved && !i.owner_met);

    if (type !== 'all') {
        active = active.filter(i => i.type === type);
    }
    if (cat !== 'all') {
        active = active.filter(i => i.category === cat);
    }
    if (loc !== 'all') {
        active = active.filter(i => i.location === loc);
    }
    if (query) {
        active = active.filter(i =>
            (i.title && i.title.toLowerCase().includes(query)) ||
            (i.description && i.description.toLowerCase().includes(query)) ||
            (i.location && i.location.toLowerCase().includes(query)) ||
            (i.category && i.category.toLowerCase().includes(query))
        );
    }

    renderItems(active);
    updateStats();
}

function renderItems(items) {
    const itemsContainer = document.getElementById('items');
    itemsContainer.innerHTML = '';

    items.forEach((item, index) => {
        const statusClass = item.type === 'found' ? 'found' : 'lost';
        const statusText = item.type === 'found' ? 'Found' : 'Lost';
        const icon = categoryIcons[item.category] || '📦';
        
        // Normalize image URL; default image if missing
        let imgSrc = item.image_url || '';
        if (imgSrc && !/^https?:\/\//i.test(imgSrc)) {
            imgSrc = `${API_BASE_URL}/${imgSrc}`;
        }
        if (!imgSrc) {
            imgSrc = 'https://images.unsplash.com/photo-1555664424-778a69022365?auto=format&fit=crop&w=400&q=80';
        }

        const card = document.createElement('div');
        card.className = 'item-card';
        card.onclick = () => showItemModal(item.id); // Use item id to resolve the correct record

        card.innerHTML = `
            <div class="item-image">
                <span class="item-status ${statusClass}">${statusText}</span>
                <img src="${imgSrc}" alt="${item.title}">
            </div>
            <div class="item-info">
                <div class="item-category"><span class="cat-icon">${icon}</span> ${item.category}</div>
                <div class="item-title">${item.title}</div>
                <div class="item-desc">${item.description}</div>
            </div>
        `;
        itemsContainer.appendChild(card);
    });
}

function renderOwnerMet(items) {
    const container = document.getElementById('ownerMetItems');
    if (!container) return;
    container.innerHTML = '';
    items.forEach(item => {
        const statusText = 'Owner Met';
        const statusClass = 'found';
        const icon = categoryIcons[item.category] || '📦';
        let imgSrc = item.image_url || '';
        if (imgSrc && !/^https?:\/\//i.test(imgSrc)) {
            imgSrc = `${API_BASE_URL}/${imgSrc}`;
        }
        if (!imgSrc) {
            imgSrc = 'https://images.unsplash.com/photo-1555664424-778a69022365?auto=format&fit=crop&w=400&q=80';
        }
        const card = document.createElement('div');
        card.className = 'item-card';
        card.innerHTML = `
            <div class="item-image">
                <span class="item-status ${statusClass}">${statusText}</span>
                <img src="${imgSrc}" alt="${item.title}">
            </div>
            <div class="item-info">
                <div class="item-category"><span class="cat-icon">${icon}</span> ${item.category}</div>
                <div class="item-title">${item.title}</div>
                <div class="item-desc">${item.description}</div>
            </div>
        `;
        container.appendChild(card);
    });
}

function updateStats() {
    const total = allItems.length;
    // Count only non-owner_met in found/lost so each item belongs to exactly one bucket
    const found = allItems.filter(i => i.type === 'found' && !i.owner_met).length;
    const lost = allItems.filter(i => i.type === 'lost' && !i.owner_met).length;
    const ownersMet = allItems.filter(i => i.owner_met).length;

    const totalEl = document.querySelector('.stat-number');
    const foundEl = document.querySelector('.stat-number.found');
    const lostEl = document.querySelector('.stat-number.lost');
    const ownerEl = document.querySelector('.stat-number.owner');

    if (totalEl) totalEl.textContent = total;
    if (foundEl) foundEl.textContent = found;
    if (lostEl) lostEl.textContent = lost;
    if (ownerEl) ownerEl.textContent = ownersMet;
}

// Modal logic for Item Details
const itemModal = document.getElementById('itemDetailsModal');
const closeItemModal = document.getElementById('closeItemModal');
let currentItemIdx = null;

// Announcement modal
const announcementModal = document.getElementById('announcementModal');
const closeAnnouncementModal = document.getElementById('closeAnnouncementModal');
const announcementModalTitle = document.getElementById('announcementModalTitle');
const announcementModalDate = document.getElementById('announcementModalDate');
const announcementModalText = document.getElementById('announcementModalText');
const announcementModalImage = document.getElementById('announcementModalImage');
const announcementModalImageWrap = document.getElementById('announcementModalImageWrap');

function showItemModal(itemRef) {
    let d = null;
    if (typeof itemRef === 'number' || typeof itemRef === 'string') {
        const idx = allItems.findIndex(i => String(i.id) === String(itemRef));
        if (idx === -1) return;
        currentItemIdx = idx;
        d = allItems[idx];
    } else if (itemRef && typeof itemRef === 'object') {
        d = itemRef;
        currentItemIdx = allItems.findIndex(i => String(i.id) === String(d.id));
    }
    if (!d) return;
    const statusClass = d.type === 'found' ? 'found' : 'lost';
    const statusText = d.type === 'found' ? 'Found' : 'Lost';
    const icon = categoryIcons[d.category] || '📦';
    let imgSrc = d.image_url || '';
    if (imgSrc && !/^https?:\/\//i.test(imgSrc)) {
        imgSrc = `${API_BASE_URL}/${imgSrc}`;
    }
    if (!imgSrc) {
        imgSrc = 'https://images.unsplash.com/photo-1555664424-778a69022365?auto=format&fit=crop&w=400&q=80';
    }

    document.getElementById('itemStatus').textContent = statusText;
    document.getElementById('itemStatus').className = 'item-status ' + statusClass;
    document.getElementById('itemCategory').innerHTML = `<span class="cat-icon">${icon}</span> ${d.category}`;
    document.getElementById('itemTitle').textContent = d.title;
    document.getElementById('itemImg').src = imgSrc;
    document.getElementById('itemImg').alt = d.title;
    document.getElementById('itemDesc').textContent = d.description;
    document.getElementById('itemLocation').textContent = d.location;
    document.getElementById('itemDate').textContent = d.date_lost;
    document.getElementById('itemOwner').textContent = d.contact_name;
    document.getElementById('itemPhone').textContent = d.contact_phone || 'Not provided';
    const ownerMetEl = document.getElementById('itemOwnerMet');
    if (ownerMetEl) ownerMetEl.textContent = d.owner_met ? 'Met' : 'Pending';
    
    itemModal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function showAnnouncementModal(ref) {
    if (!announcementModal) return;
    let record = null;
    if (ref && typeof ref === 'object') {
        record = ref;
    } else {
        record = announcementsData.find(a => String(a.id) === String(ref));
    }
    if (!record) return;

    const imgSrc = resolveImageUrl(record.image_url);
    const safeDate = formatDateDisplay(record.created_at);

    if (announcementModalTitle) announcementModalTitle.textContent = record.title || 'Announcement';
    if (announcementModalDate) announcementModalDate.textContent = safeDate;
    if (announcementModalText) announcementModalText.textContent = record.message || '';

    if (announcementModalImageWrap && announcementModalImage) {
        if (imgSrc) {
            announcementModalImageWrap.style.display = 'block';
            announcementModalImage.src = imgSrc;
            announcementModalImage.alt = record.title || 'Announcement image';
        } else {
            announcementModalImageWrap.style.display = 'none';
            announcementModalImage.removeAttribute('src');
            announcementModalImage.alt = '';
        }
    }

    announcementModal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function hideAnnouncementModal() {
    if (!announcementModal) return;
    announcementModal.style.display = 'none';
    document.body.style.overflow = '';
}

closeItemModal.onclick = function() {
    itemModal.style.display = 'none';
    document.body.style.overflow = '';
};

if (closeAnnouncementModal) {
    closeAnnouncementModal.onclick = hideAnnouncementModal;
}

window.onclick = function(e) {
    if (e.target === itemModal) {
        itemModal.style.display = 'none';
        document.body.style.overflow = '';
    }
    if (e.target === announcementModal) {
        hideAnnouncementModal();
    }
    if (e.target === modal) {
        hideModal();
    }
};

// Handle Form Submission
const reportForm = document.getElementById('reportForm');

reportForm.addEventListener('submit', async function(e) {
    e.preventDefault();

    const fd = new FormData();
    fd.append('title', document.getElementById('reportTitle').value);
    fd.append('description', document.getElementById('reportDesc').value);
    fd.append('category', document.getElementById('reportCategory').value);
    fd.append('location', document.getElementById('reportLocation').value);
    fd.append('date_lost', document.getElementById('reportDate').value);
    fd.append('contact_name', document.getElementById('reportName').value);
    fd.append('contact_phone', document.getElementById('reportPhone').value);
    fd.append('manage_pin', document.getElementById('reportPin').value);
    fd.append('type', currentType);

    const imageFile = document.getElementById('reportImage').files[0];
    if (imageFile) {
        fd.append('image', imageFile);
    }

    try {
        const response = await fetch(`${API_BASE_URL}/report.php`, {
            method: 'POST',
            body: fd
        });

        const raw = await response.text();
        let result;
        try { result = JSON.parse(raw); } catch (e) { result = null; }

        if (response.ok) {
            alert('Item reported successfully!');
            reportForm.reset();
            hideModal();
            fetchItems(); // Refresh the list
        } else {
            const message = (result && result.message) ? result.message : (raw || 'Unknown server error');
            alert('Error: ' + message);
            console.error('Report error response:', raw);
        }
    } catch (error) {
        console.error('Error submitting report:', error);
        alert('An error occurred while submitting the report: ' + error.message);
    }
});

// Copy phone
document.getElementById('copyPhone').onclick = function() {
    const phone = document.getElementById('itemPhone').textContent;
    navigator.clipboard.writeText(phone);
};
// Contact Owner button
document.getElementById('contactOwnerBtn').onclick = function() {
    const phone = document.getElementById('itemPhone').textContent;
    if (phone && phone !== 'Not provided') {
        window.location.href = 'tel:' + phone.replace(/[^+\d]/g, '');
    }
};

// Status update button (requires Manage PIN)
const markOwnerMetBtn = document.getElementById('markOwnerMetBtn');

async function updateStatus(field) {
    if (currentItemIdx === null) return;
    const item = allItems[currentItemIdx];
    const pin = prompt('Enter your Manage PIN to update status:');
    if (!pin) return;
    try {
        const response = await fetch(`${API_BASE_URL}/update_status.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: item.id, field, manage_pin: pin })
        });
        const raw = await response.text();
        let result;
        try { result = JSON.parse(raw); } catch (e) { result = null; }
        if (!response.ok) throw new Error((result && result.message) || raw || 'Status update failed');
        await fetchItems();
        alert('Status updated');
        itemModal.style.display = 'none';
        document.body.style.overflow = '';
    } catch (err) {
        alert('Error updating status: ' + err.message);
    }
}

markOwnerMetBtn.onclick = () => updateStatus('owner_met');

// Reviews
const reviewForm = document.getElementById('reviewForm');
const reviewList = document.getElementById('reviewList');

async function fetchReviews() {
    try {
        const response = await fetch(`${API_BASE_URL}/reviews.php`);
        const raw = await response.text();
        const data = JSON.parse(raw);
        reviewList.innerHTML = '';
        data.forEach(r => {
            const div = document.createElement('div');
            div.className = 'review-item';
            const safeRating = Math.max(1, Math.min(5, parseInt(r.rating, 10) || 5));
            div.innerHTML = `<div class="review-header"><strong>${r.name}</strong> • ${'★'.repeat(safeRating)}${'☆'.repeat(5 - safeRating)}</div><div class="review-body">${r.comment}</div>`;
            reviewList.appendChild(div);
        });
    } catch (err) {
        console.error('Failed to load reviews', err);
    }
}

reviewForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const payload = {
        name: document.getElementById('reviewName').value,
        rating: document.getElementById('reviewRating').value,
        comment: document.getElementById('reviewComment').value
    };
    try {
        const resp = await fetch(`${API_BASE_URL}/reviews.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const raw = await resp.text();
        let data;
        try { data = JSON.parse(raw); } catch { data = null; }
        if (!resp.ok) throw new Error((data && data.message) || raw || 'Review failed');
        reviewForm.reset();
        fetchReviews();
        alert('Thanks for your review!');
    } catch (err) {
        alert('Could not submit review: ' + err.message);
    }
});

// Fetch Announcements
async function fetchAnnouncements() {
    try {
        const url = '/php/announcements.php';
        console.log('Fetching announcements from:', url);
        const response = await fetch(url, { cache: 'no-store' });
        const raw = await response.text();

        if (!response.ok) {
            console.error('Announcements request failed:', response.status, response.statusText, raw);
            announcementsData = [];
            return;
        }

        let announcements;
        try {
            announcements = JSON.parse(raw);
        } catch (e) {
            console.error("Announcements non-JSON response:", raw);
            announcementsData = [];
            // Don't throw, just log, so the rest of the page works
            return; 
        }

        if (!Array.isArray(announcements)) {
            console.error('Announcements response was JSON but not an array:', announcements);
            announcementsData = [];
            return;
        }

        const container = document.getElementById('announcementList');
        const section = document.getElementById('announcements');

        console.log('announcementList:', container);
        console.log('announcements section:', section);

        if (!container || !section) {
            console.warn('Announcements DOM not found');
            return;
        }


        if (announcements.length > 0) {
            announcementsData = announcements;
            section.style.display = 'block';
            container.innerHTML = '';

            announcements.forEach(a => {
                const card = document.createElement('div');
                card.className = 'announcement-card';
                card.setAttribute('role', 'button');
                card.tabIndex = 0;

                const imgSrc = resolveImageUrl(a.image_url);
                if (imgSrc) {
                    const img = document.createElement('img');
                    img.className = 'announcement-img';
                    img.src = imgSrc;
                    img.alt = a.title || 'Announcement image';
                    card.appendChild(img);
                }

                const body = document.createElement('div');
                body.className = 'announcement-body';

                const title = document.createElement('div');
                title.className = 'announcement-title';
                title.textContent = a.title || 'Announcement';

                const date = document.createElement('small');
                date.className = 'announcement-date';
                date.textContent = formatDateDisplay(a.created_at) || '';

                const text = document.createElement('div');
                text.className = 'announcement-text';
                text.textContent = a.message || '';

                body.appendChild(title);
                if (date.textContent) body.appendChild(date);
                body.appendChild(text);

                card.appendChild(body);

                const open = () => showAnnouncementModal(a);
                card.addEventListener('click', open);
                card.addEventListener('keypress', (evt) => {
                    if (evt.key === 'Enter' || evt.key === ' ') {
                        evt.preventDefault();
                        open();
                    }
                });

                container.appendChild(card);
            });
        } else {
            announcementsData = [];
            section.style.display = 'none';
            container.innerHTML = '';
        }
    } catch (error) {
        announcementsData = [];
        console.error('Error fetching announcements:', error);
    }
}

// Initial Fetch
document.addEventListener('DOMContentLoaded', () => {
    fetchItems();
    fetchReviews();
    fetchAnnouncements();

    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (typeFilter) typeFilter.addEventListener('change', applyFilters);
    if (categoryFilter) categoryFilter.addEventListener('change', applyFilters);
    if (locationFilter) locationFilter.addEventListener('change', applyFilters);
});