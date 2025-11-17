import { onMounted, onUnmounted } from 'vue';

/**
 * Composable para configurar mensajes de validación HTML5 en español
 * 
 * @example
 * // En cualquier componente Vue:
 * import { useHtml5Validation } from '@/composables/useHtml5Validation';
 * 
 * // En el setup:
 * useHtml5Validation();
 */
export function useHtml5Validation() {
    const validationMessages = {
        // Mensajes por tipo de error
        valueMissing: 'Por favor, completa este campo.',
        typeMismatch: {
            email: 'Por favor, ingresa un correo electrónico válido.',
            url: 'Por favor, ingresa una URL válida.',
            tel: 'Por favor, ingresa un número de teléfono válido.',
        },
        tooShort: (minLength) => `Debe tener al menos ${minLength} caracteres.`,
        tooLong: (maxLength) => `Debe tener como máximo ${maxLength} caracteres.`,
        rangeUnderflow: (min) => `El valor debe ser mayor o igual a ${min}.`,
        rangeOverflow: (max) => `El valor debe ser menor o igual a ${max}.`,
        stepMismatch: 'Por favor, ingresa un valor válido.',
        patternMismatch: 'Por favor, sigue el formato solicitado.',
        badInput: 'Por favor, ingresa un valor válido.',
    };

    const handleInvalid = (e) => {
        const input = e.target;
        input.setCustomValidity('');

        if (!input.validity.valid) {
            if (input.validity.valueMissing) {
                input.setCustomValidity(validationMessages.valueMissing);
            } else if (input.validity.typeMismatch) {
                const type = input.type;
                input.setCustomValidity(
                    validationMessages.typeMismatch[type] || 'Por favor, ingresa un valor válido.'
                );
            } else if (input.validity.tooShort) {
                input.setCustomValidity(validationMessages.tooShort(input.minLength));
            } else if (input.validity.tooLong) {
                input.setCustomValidity(validationMessages.tooLong(input.maxLength));
            } else if (input.validity.rangeUnderflow) {
                input.setCustomValidity(validationMessages.rangeUnderflow(input.min));
            } else if (input.validity.rangeOverflow) {
                input.setCustomValidity(validationMessages.rangeOverflow(input.max));
            } else if (input.validity.stepMismatch) {
                input.setCustomValidity(validationMessages.stepMismatch);
            } else if (input.validity.patternMismatch) {
                input.setCustomValidity(validationMessages.patternMismatch);
            } else if (input.validity.badInput) {
                input.setCustomValidity(validationMessages.badInput);
            }
        }
    };

    const handleInput = (e) => {
        e.target.setCustomValidity('');
    };

    onMounted(() => {
        const inputs = document.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('invalid', handleInvalid);
            input.addEventListener('input', handleInput);
        });
    });

    onUnmounted(() => {
        const inputs = document.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.removeEventListener('invalid', handleInvalid);
            input.removeEventListener('input', handleInput);
        });
    });

    return {
        validationMessages,
    };
}
