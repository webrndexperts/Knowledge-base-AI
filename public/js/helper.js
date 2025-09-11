/**
 * fireConfirmationAlert: Function to trigger a confirmation alert before proceeding with an action.
 *
 * @param {string} message - The message to display in the confirmation alert.
 * @param {string} [icon='info'] - The icon to display in the alert (e.g., 'info', 'warning', 'error'). Default is 'info'.
 * @param {string} [confirmText='Confirm'] - The text for the confirm button. Default is 'Confirm'.
 * @param {string} [cancelText='Cancel'] - The text for the cancel button. Default is 'Cancel'.
 *
 * @returns {Promise<boolean>} - A Promise that resolves to a boolean value: `true` if the user confirms the action, `false` if they cancel.
 */
async function fireConfirmationAlert(
    message = "",
    icon = "info",
    confirmText = "Confirm",
    cancelText = "Cancel"
) {
    let result = await Swal.fire({
        title: "Are you sure!",
        text: message,
        icon,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
    });

    return result.isConfirmed;
}

function toast(message, type = "success") {
    let bg = "#4BB543";

    if (type === "error") {
        bg = "#DC3545";
    } else if (type === "warning") {
        bg = "#FFC107";
    } else if (type === "info") {
        bg = "#0DCAF0";
    }

    Toastify({
        text: message,
        duration: 3000,
        gravity: "top",
        position: "right",
        style: {
            background: bg,
        },
    }).showToast();
}
