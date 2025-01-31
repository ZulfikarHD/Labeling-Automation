import { inject } from 'vue';

export function useNotification() {
  const swal = inject('$swal');

  const showSuccessNotification = (title, message) => {
    return swal.fire({
      icon: 'success',
      title,
      text: message,
      customClass: {
        popup: 'rounded-lg',
        title: 'text-xl font-bold text-green-600 mb-4',
        htmlContainer: 'text-base text-gray-600',
        confirmButton: 'bg-green-500 hover:bg-green-600 text-white font-medium rounded-lg px-4 py-2'
      },
      iconColor: '#22c55e'
    });
  };

  const showErrorNotification = (title, errors) => {
    const errorMessage = Array.isArray(errors) ? errors.join('<br>') : errors;

    return swal.fire({
      icon: 'error',
      title,
      html: `<div class="text-left">
        <p class="text-red-500 font-medium text-lg mb-2">Error:</p>
        <ul class="text-gray-700 text-base space-y-1 list-disc pl-5">
          ${errorMessage.split('<br>').map(error => `<li>${error}</li>`).join('')}
        </ul>
      </div>`,
      customClass: {
        popup: 'rounded-lg',
        title: 'text-xl font-bold text-red-600 mb-4',
        htmlContainer: 'p-4',
        confirmButton: 'bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg px-4 py-2'
      },
      iconColor: '#ef4444'
    });
  };

  const showConfirmation = (title, message) => {
    return swal.fire({
      title,
      text: message,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya',
      cancelButtonText: 'Tidak',
      reverseButtons: true,
      customClass: {
        popup: 'rounded-lg',
        title: 'text-xl font-bold text-gray-800 dark:text-gray-200 mb-4',
        htmlContainer: 'text-base text-gray-600 dark:text-gray-400',
        confirmButton: 'bg-cyan-500 hover:bg-cyan-600 text-white font-medium rounded-lg px-4 py-2',
        cancelButton: 'bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg px-4 py-2 ml-2'
      },
      iconColor: '#0891b2'
    });
  };

  return {
    showSuccessNotification,
    showErrorNotification,
    showConfirmation
  };
}
