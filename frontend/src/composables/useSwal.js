import Swal from 'sweetalert2'

const swalConfig = {
  confirmButtonColor: '#4f46e5',
  cancelButtonColor: '#64748b',
}

export function useSwal() {
  const success = (title, text = '') =>
    Swal.fire({ icon: 'success', title, text, ...swalConfig })

  const error = (title, text = '') =>
    Swal.fire({ icon: 'error', title, text, ...swalConfig })

  const confirmDelete = (title = 'Excluir registro?', text = 'Esta ação não pode ser desfeita.') =>
    Swal.fire({
      icon: 'warning',
      title,
      text,
      showCancelButton: true,
      confirmButtonText: 'Sim, excluir',
      cancelButtonText: 'Cancelar',
      ...swalConfig,
    })

  const confirmAction = (title, text, confirmText = 'Confirmar') =>
    Swal.fire({
      icon: 'question',
      title,
      text,
      showCancelButton: true,
      confirmButtonText: confirmText,
      cancelButtonText: 'Cancelar',
      ...swalConfig,
    })

  return { success, error, confirmDelete, confirmAction }
}
