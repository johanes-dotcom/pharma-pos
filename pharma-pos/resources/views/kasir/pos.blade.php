@extends('layouts.app')

@section('title', 'POS - PharmaPOS')

@section('styles')
<style>
    .pos-container {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 20px;
        height: calc(100vh - 200px);
    }
    
    .product-list {
        overflow-y: auto;
        max-height: 100%;
    }
    
    .product-card {
        cursor: pointer;
        transition: all 0.2s;
        border: 2px solid transparent;
    }
    
    .product-card:hover {
        transform: translateY(-2px);
        border-color: var(--primary-color);
    }
    
    .product-card.selected {
        border-color: var(--success-color);
        background-color: #d1e7dd;
    }
    
    .cart-item {
        border-bottom: 1px solid #eee;
        padding: 10px 0;
    }
    
    .cart-item:last-child {
        border-bottom: none;
    }
    
    .search-box {
        position: relative;
    }
    
    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }
    
    .search-box input {
        padding-left: 40px;
    }
    
    .total-section {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
    }
    
    .btn-checkout {
        background: linear-gradient(135deg, #198754 0%, #146c43 100%);
        border: none;
        font-size: 1.2rem;
        padding: 15px;
    }
    
    .btn-checkout:hover {
        background: linear-gradient(135deg, #146c43 0%, #0f5132 100%);
    }
    
    .product-img {
        width: 60px;
        height: 60px;
        background: #f8f9fa;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #6c757d;
    }
    
    .category-badge {
        cursor: pointer;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.85rem;
        border: 1px solid #dee2e6;
        transition: all 0.2s;
    }
    
    .category-badge:hover, .category-badge.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }
    
    .quantity-control {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .quantity-control button {
        width: 28px;
        height: 28px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .stok-warning {
        color: #dc3545;
        font-size: 0.75rem;
    }
</style>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h4 class="fw-bold"><i class="fas fa-cash-register me-2"></i>Point of Sale</h4>
    </div>
    <div class="col-md-4 text-end">
        <span class="text-muted">
            <i class="fas fa-user me-1"></i> {{ Auth::user()->name }}
        </span>
    </div>
</div>

<div class="pos-container">
    <!-- Product List -->
    <div class="card">
        <div class="card-body">
            <!-- Search & Category -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" class="form-control" id="searchProduct" placeholder="Cari obat...">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-2 overflow-auto py-2" id="categoryFilter">
                        <span class="category-badge active" data-id="all">Semua</span>
                        @foreach($obats->pluck('kategori')->unique() as $kategori)
                        @if($kategori)
                        <span class="category-badge" data-id="{{ $kategori->id }}">{{ $kategori->nama_kategori }}</span>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- Product Grid -->
            <div class="row" id="productGrid">
                @foreach($obats as $obat)
                <div class="col-md-3 col-6 mb-3 product-item" 
                     data-id="{{ $obat->id }}" 
                     data-name="{{ $obat->nama_obat }}"
                     data-price="{{ $obat->harga_jual }}"
                     data-stock="{{ $obat->stok }}"
                     data-category="{{ $obat->kategori_id ?? '' }}">
                    <div class="card product-card h-100" onclick="addToCart({{ $obat->id }})">
                        <div class="card-body text-center p-2">
                            <div class="product-img mb-2">
                                <i class="fas fa-capsules"></i>
                            </div>
                            <h6 class="mb-1 text-truncate">{{ $obat->nama_obat }}</h6>
                            <p class="text-primary fw-bold mb-1">Rp {{ number_format($obat->harga_jual, 0, ',', '.') }}</p>
                            @if($obat->stok <= $obat->stok_minimum)
                            <span class="stok-warning"><i class="fas fa-exclamation-triangle"></i> Stok: {{ $obat->stok }}</span>
                            @else
                            <small class="text-muted">Stok: {{ $obat->stok }}</small>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <!-- Cart Section -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Keranjang</h5>
        </div>
        <div class="card-body" style="overflow-y: auto;">
            <!-- Pelanggan Select -->
            <div class="mb-3">
                <label class="form-label">Pelanggan (Opsional)</label>
                <select class="form-select" id="pelangganSelect">
                    <option value="">-- Umum --</option>
                    @foreach($pelanggans as $pelanggan)
                    <option value="{{ $pelanggan->id }}">{{ $pelanggan->nama }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Cart Items -->
            <div id="cartItems">
                <p class="text-center text-muted py-5">
                    <i class="fas fa-shopping-basket fa-3x mb-3"></i><br>
                    Keranjang kosong<br>
                    Klik produk untuk menambah
                </p>
            </div>
        </div>
        
        <!-- Total & Checkout -->
        <div class="card-footer">
            <div class="total-section mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Subtotal</span>
                    <span id="subtotalDisplay">Rp 0</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Diskon</span>
                    <input type="number" class="form-control text-end" id="diskonInput" 
                           value="0" min="0" style="width: 120px;" onchange="updateTotal()">
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">TOTAL</h4>
                    <h4 class="mb-0" id="totalDisplay">Rp 0</h4>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Bayar</label>
                <input type="number" class="form-control form-control-lg" id="bayarInput" 
                       placeholder="Masukkan jumlah pembayaran" oninput="calculateChange()">
            </div>
            
            <div class="mb-3">
                <div class="d-flex justify-content-between">
                    <span>Kembalian</span>
                    <span class="fw-bold text-success" id="kembalianDisplay">Rp 0</span>
                </div>
            </div>
            
            <button class="btn btn-success btn-checkout w-100" onclick="checkout()" id="checkoutBtn" disabled>
                <i class="fas fa-check-circle me-2"></i>Checkout
            </button>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Transaksi Berhasil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="strukContent">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="printStruk()">
                    <i class="fas fa-print me-2"></i>Print
                </button>
                <button type="button" class="btn btn-primary" onclick="newTransaction()">
                    <i class="fas fa-plus me-2"></i>Transaksi Baru
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let cart = [];
    let products = @json($obats);
    
    // Add to cart
    function addToCart(productId) {
        const product = products.find(p => p.id === productId);
        if (!product) return;
        
        if (product.stok <= 0) {
            alert('Stok produk habis!');
            return;
        }
        
        const existingItem = cart.find(item => item.obat_id === productId);
        
        if (existingItem) {
            if (existingItem.jumlah >= product.stok) {
                alert('Stok tidak cukup!');
                return;
            }
            existingItem.jumlah++;
        } else {
            cart.push({
                obat_id: productId,
                nama_obat: product.nama_obat,
                harga_satuan: product.harga_jual,
                jumlah: 1,
                stok: product.stok
            });
        }
        
        renderCart();
    }
    
    // Render cart
    function renderCart() {
        const cartContainer = document.getElementById('cartItems');
        
        if (cart.length === 0) {
            cartContainer.innerHTML = `
                <p class="text-center text-muted py-5">
                    <i class="fas fa-shopping-basket fa-3x mb-3"></i><br>
                    Keranjang kosong<br>
                    Klik produk untuk menambah
                </p>
            `;
            updateTotal();
            return;
        }
        
        let html = '';
        cart.forEach((item, index) => {
            const subtotal = item.jumlah * item.harga_satuan;
            html += `
                <div class="cart-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">${item.nama_obat}</h6>
                            <small class="text-muted">Rp ${formatNumber(item.harga_satuan)} x ${item.jumlah}</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">Rp ${formatNumber(subtotal)}</div>
                            <div class="quantity-control mt-1">
                                <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${index}, -1)">-</button>
                                <span class="px-2">${item.jumlah}</span>
                                <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${index}, 1)">+</button>
                                <button class="btn btn-sm btn-outline-danger ms-2" onclick="removeFromCart(${index})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        cartContainer.innerHTML = html;
        updateTotal();
    }
    
    // Update quantity
    function updateQuantity(index, change) {
        const item = cart[index];
        const newQuantity = item.jumlah + change;
        
        if (newQuantity <= 0) {
            removeFromCart(index);
            return;
        }
        
        if (newQuantity > item.stok) {
            alert('Stok tidak cukup!');
            return;
        }
        
        item.jumlah = newQuantity;
        renderCart();
    }
    
    // Remove from cart
    function removeFromCart(index) {
        cart.splice(index, 1);
        renderCart();
    }
    
    // Update total
    function updateTotal() {
        const diskon = parseFloat(document.getElementById('diskonInput').value) || 0;
        const subtotal = cart.reduce((sum, item) => sum + (item.jumlah * item.harga_satuan), 0);
        const total = subtotal - diskon;
        
        document.getElementById('subtotalDisplay').textContent = 'Rp ' + formatNumber(subtotal);
        document.getElementById('totalDisplay').textContent = 'Rp ' + formatNumber(total);
        
        calculateChange();
    }
    
    // Calculate change
    function calculateChange() {
        const diskon = parseFloat(document.getElementById('diskonInput').value) || 0;
        const subtotal = cart.reduce((sum, item) => sum + (item.jumlah * item.harga_satuan), 0);
        const total = subtotal - diskon;
        const bayar = parseFloat(document.getElementById('bayarInput').value) || 0;
        const kembalian = Math.max(0, bayar - total);
        
        document.getElementById('kembalianDisplay').textContent = 'Rp ' + formatNumber(kembalian);
        
        const checkoutBtn = document.getElementById('checkoutBtn');
        if (bayar >= total && cart.length > 0) {
            checkoutBtn.disabled = false;
        } else {
            checkoutBtn.disabled = true;
        }
    }
    
    // Format number
    function formatNumber(num) {
        return Math.floor(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    
    // Checkout
    function checkout() {
        const diskon = parseFloat(document.getElementById('diskonInput').value) || 0;
        const bayar = parseFloat(document.getElementById('bayarInput').value) || 0;
        
        const diskonTotal = cart.reduce((sum, item) => sum + (item.jumlah * item.harga_satuan), 0) - (cart.reduce((sum, item) => sum + (item.jumlah * item.harga_satuan), 0) - diskon);
        
        const data = {
            items: cart,
            pelanggan_id: document.getElementById('pelangganSelect').value || null,
            diskon: diskon,
            bayar: bayar,
            _token: '{{ csrf_token() }}'
        };
        
        fetch('{{ route("pos.proses") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showStruk(data.penjualan);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memproses transaksi');
        });
    }
    
    // Show struk
    function showStruk(penjualan) {
        const content = `
            <div class="text-center">
                <h5>PharmaPOS</h5>
                <p class="mb-0">Sistem Penjualan Apotek</p>
                <hr>
                <p class="mb-1">No: ${penjualan.kode_penjualan}</p>
                <p class="mb-1">Kasir: {{ Auth::user()->name }}</p>
                <p class="mb-3">${new Date().toLocaleString('id-ID')}</p>
                <hr>
                ${cart.map(item => `
                    <div class="d-flex justify-content-between">
                        <span>${item.nama_obat} x${item.jumlah}</span>
                        <span>Rp ${formatNumber(item.jumlah * item.harga_satuan)}</span>
                    </div>
                `).join('')}
                <hr>
                <div class="d-flex justify-content-between">
                    <span>Subtotal</span>
                    <span>Rp ${formatNumber(penjualan.total + penjualan.diskon)}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Diskon</span>
                    <span>- Rp ${formatNumber(penjualan.diskon)}</span>
                </div>
                <div class="d-flex justify-content-between fw-bold">
                    <span>TOTAL</span>
                    <span>Rp ${formatNumber(penjualan.total)}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Bayar</span>
                    <span>Rp ${formatNumber(penjualan.bayar)}</span>
                </div>
                <div class="d-flex justify-content-between text-success">
                    <span>Kembalian</span>
                    <span>Rp ${formatNumber(penjualan.kembalian)}</span>
                </div>
                <hr>
                <p class="mb-0">Terima kasih atas kunjungan Anda</p>
                <p class="text-muted small">Silakan bawa struk ini sebagai bukti</p>
            </div>
        `;
        
        document.getElementById('strukContent').innerHTML = content;
        new bootstrap.Modal(document.getElementById('successModal')).show();
    }
    
    // Print struk
    function printStruk() {
        const content = document.getElementById('strukContent').innerHTML;
        const printWindow = window.open('', '', 'height=500,width=300');
        printWindow.document.write('<html><head><title>Struk</title>');
        printWindow.document.write('<style>body{font-family:Arial,sans-serif;font-size:12px;}</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write(content);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    }
    
    // New transaction
    function newTransaction() {
        cart = [];
        document.getElementById('pelangganSelect').value = '';
        document.getElementById('diskonInput').value = 0;
        document.getElementById('bayarInput').value = '';
        bootstrap.Modal.getInstance(document.getElementById('successModal')).hide();
        renderCart();
    }
    
    // Search product
    document.getElementById('searchProduct').addEventListener('input', function(e) {
        const search = e.target.value.toLowerCase();
        document.querySelectorAll('.product-item').forEach(item => {
            const name = item.dataset.name.toLowerCase();
            item.style.display = name.includes(search) ? 'block' : 'none';
        });
    });
    
    // Category filter
    document.getElementById('categoryFilter').addEventListener('click', function(e) {
        if (e.target.classList.contains('category-badge')) {
            document.querySelectorAll('.category-badge').forEach(b => b.classList.remove('active'));
            e.target.classList.add('active');
            
            const categoryId = e.target.dataset.id;
            document.querySelectorAll('.product-item').forEach(item => {
                if (categoryId === 'all' || item.dataset.category === categoryId) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }
    });
</script>
@endsection
