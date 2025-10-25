// User Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Toggle user status with AJAX
    const toggleStatusForms = document.querySelectorAll('form[action*="toggle-status"]');
    toggleStatusForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const button = this.querySelector('button[type="submit"]');
            const originalText = button.textContent;
            const originalClass = button.className;
            
            // Show loading state
            button.textContent = 'Đang xử lý...';
            button.disabled = true;
            button.className = originalClass.replace(/btn-(success|danger)/, 'btn-secondary');
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: new URLSearchParams(new FormData(this))
            })
            .then(response => response.text())
            .then(html => {
                // Reload page to show updated status
                window.location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi cập nhật trạng thái người dùng');
                
                // Restore button state
                button.textContent = originalText;
                button.disabled = false;
                button.className = originalClass;
            });
        });
    });

    // Enhanced delete confirmation
    const deleteForms = document.querySelectorAll('form[action*="destroy"]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const userName = this.closest('tr').querySelector('td:nth-child(2)').textContent;
            
            if (confirm(`Bạn có chắc chắn muốn xóa người dùng "${userName}"?\n\nHành động này không thể hoàn tác!`)) {
                this.submit();
            }
        });
    });

    // Auto-submit search form on Enter
    const searchInput = document.getElementById('search');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.closest('form').submit();
            }
        });
    }

    // Preserve search parameters when using filter dropdowns (only on index page)
    const searchForm = document.querySelector('form[action*="users"][method="GET"]');
    if (searchForm) {
        const filterSelects = searchForm.querySelectorAll('select[name="role_id"], select[name="is_active"]');
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                // Auto-submit form when filter changes (only for search form)
                searchForm.submit();
            });
        });
    }

    // Add loading state to search form
    const searchFormForLoading = document.querySelector('form[action*="users"][method="GET"]');
    if (searchFormForLoading) {
        searchFormForLoading.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tìm...';
                submitBtn.disabled = true;
            }
        });
    }

    // Clear search input with escape key
    if (searchInput) {
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                this.focus();
            }
        });
    }

    // Prevent accidental form submission on create/edit forms
    const createEditForms = document.querySelectorAll('form[action*="store"], form[action*="update"]');
    createEditForms.forEach(form => {
        // Prevent Enter key from submitting form accidentally
        form.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'BUTTON') {
                e.preventDefault();
            }
        });
        
        // Add confirmation for form submission
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            }
        });
    });

    // Province-District dependency
    const provinceSelect = document.getElementById('province_id');
    const districtSelect = document.getElementById('district_id');
    
    if (provinceSelect && districtSelect) {
        provinceSelect.addEventListener('change', function() {
            const provinceId = this.value;
            
            // Clear district options
            districtSelect.innerHTML = '<option value="">Chọn quận/huyện</option>';
            
            if (provinceId) {
                // Fetch districts for selected province
                fetch(`/api/districts?province_id=${provinceId}`)
                    .then(response => response.json())
                    .then(districts => {
                        districts.forEach(district => {
                            const option = document.createElement('option');
                            option.value = district.id;
                            option.textContent = district.name;
                            districtSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching districts:', error);
                    });
            }
        });
    }

    // Show success/error messages
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
});
