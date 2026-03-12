import Swal from 'sweetalert2';

export function useAlert() {
    const showSuccess = (title = 'Berhasil!', text = 'Data berhasil disimpan') => {
        return Swal.fire({
            icon: 'success',
            title: title,
            text: text,
            showConfirmButton: false,
            timer: 1500
        });
    };

    const showError = (title = 'Gagal!', text = 'Terjadi kesalahan') => {
        return Swal.fire({
            icon: 'error',
            title: title,
            text: text
        });
    };

    const showConfirm = async (title = 'Apakah Anda yakin?', text = "Data yang dihapus tidak dapat dikembalikan") => {
        const result = await Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        });
        return result.isConfirmed;
    };

    return {
        showSuccess,
        showError,
        showConfirm
    };
}
