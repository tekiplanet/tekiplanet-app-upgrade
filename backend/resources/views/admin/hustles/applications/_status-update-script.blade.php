<script>
async function updateApplicationStatus(url, status, action) {
    const result = await Swal.fire({
        title: `${action} Application`,
        text: `Are you sure you want to ${action.toLowerCase()} this application?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: action,
        cancelButtonText: 'Cancel',
        confirmButtonColor: status === 'approved' ? '#10B981' : '#EF4444',
        reverseButtons: true,
        showLoaderOnConfirm: true,
        preConfirm: async () => {
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        _method: 'PATCH',
                        status: status
                    })
                });

                if (!response.ok) {
                    throw new Error('Failed to update application status');
                }

                return response.json();
            } catch (error) {
                Swal.showValidationMessage(`Request failed: ${error}`);
            }
        },
        allowOutsideClick: () => !Swal.isLoading()
    });

    if (result.isConfirmed) {
        await Swal.fire({
            title: 'Success!',
            text: 'Application status updated successfully',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
        });
        
        // Reload the page to show updated status
        window.location.reload();
    }
}

async function updateHustleStatus(url, status) {
    const result = await Swal.fire({
        title: 'Update Hustle Status',
        text: `Are you sure you want to mark this hustle as ${status.replace('_', ' ')}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, update it',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        showLoaderOnConfirm: true,
        preConfirm: async () => {
            try {
                const response = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status })
                });

                if (!response.ok) {
                    throw new Error('Failed to update status');
                }

                return response.json();
            } catch (error) {
                Swal.showValidationMessage(`Request failed: ${error}`);
            }
        },
        allowOutsideClick: () => !Swal.isLoading()
    });

    if (result.isConfirmed) {
        await Swal.fire({
            title: 'Success!',
            text: 'Hustle status updated successfully',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
        });
        
        window.location.reload();
    }
}

async function updatePaymentStatus(url, status) {
    const result = await Swal.fire({
        title: 'Complete Payment',
        text: 'Are you sure you want to mark this payment as completed?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, complete it',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#10B981',
        reverseButtons: true,
        showLoaderOnConfirm: true,
        preConfirm: async () => {
            try {
                const response = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status })
                });

                if (!response.ok) {
                    throw new Error('Failed to update payment status');
                }

                return response.json();
            } catch (error) {
                Swal.showValidationMessage(`Request failed: ${error}`);
            }
        },
        allowOutsideClick: () => !Swal.isLoading()
    });

    if (result.isConfirmed) {
        await Swal.fire({
            title: 'Success!',
            text: 'Payment status updated successfully',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
        });
        
        window.location.reload();
    }
}
</script> 