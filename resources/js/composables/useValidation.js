import { reactive, computed } from 'vue';

/**
 * Composable para validaciones de formularios usando las traducciones de Laravel
 * 
 * @example
 * import { useValidation } from '@/composables/useValidation';
 * 
 * const { validate, errors, hasError, getError, clearError, clearErrors } = useValidation();
 * 
 * const rules = {
 *   nombre: ['required', 'min:3', 'max:255'],
 *   email: ['required', 'email', 'unique:usuarios,correo'],
 *   password: ['required', 'min:8', 'confirmed']
 * };
 * 
 * if (!validate(formData, rules)) {
 *   console.log(errors); // { nombre: 'El campo nombre es obligatorio.' }
 * }
 */

const validationMessages = {
    required: (attribute) => `El campo ${attribute} es obligatorio.`,
    email: (attribute) => `El campo ${attribute} debe ser una dirección de correo válida.`,
    min: (attribute, min) => `El campo ${attribute} debe tener al menos ${min} caracteres.`,
    max: (attribute, max) => `El campo ${attribute} no debe tener más de ${max} caracteres.`,
    confirmed: (attribute) => `La confirmación de ${attribute} no coincide.`,
    numeric: (attribute) => `El campo ${attribute} debe ser un número.`,
    unique: (attribute) => `El valor del campo ${attribute} ya está en uso.`,
    alpha: (attribute) => `El campo ${attribute} solo debe contener letras.`,
    alpha_num: (attribute) => `El campo ${attribute} solo debe contener letras y números.`,
    between: (attribute, min, max) => `El campo ${attribute} debe estar entre ${min} y ${max}.`,
    integer: (attribute) => `El campo ${attribute} debe ser un número entero.`,
    url: (attribute) => `El campo ${attribute} debe ser una URL válida.`,
    date: (attribute) => `El campo ${attribute} no es una fecha válida.`,
    regex: (attribute) => `El formato del campo ${attribute} no es válido.`,
    in: (attribute, values) => `El ${attribute} seleccionado es inválido.`,
    same: (attribute, other) => `Los campos ${attribute} y ${other} deben coincidir.`,
};

const attributeNames = {
    nombre: 'nombre',
    apellido: 'apellido',
    ci: 'CI',
    telefono: 'teléfono',
    email: 'correo electrónico',
    password: 'contraseña',
    password_confirmation: 'confirmación de contraseña',
    telefono: 'teléfono',
};

export function useValidation() {
    const errors = reactive({});

    const validateField = (field, value, rules) => {
        const fieldName = attributeNames[field] || field;
        
        for (const rule of rules) {
            // Parsear regla (ej: "min:8" -> { name: 'min', params: ['8'] })
            const [ruleName, ...ruleParams] = rule.split(':');
            const params = ruleParams.length > 0 ? ruleParams[0].split(',') : [];

            // Regla: required
            if (ruleName === 'required') {
                if (!value || (typeof value === 'string' && value.trim() === '')) {
                    return validationMessages.required(fieldName);
                }
            }

            // Regla: email
            if (ruleName === 'email' && value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    return validationMessages.email(fieldName);
                }
            }

            // Regla: min
            if (ruleName === 'min' && value) {
                const minLength = parseInt(params[0]);
                if (value.length < minLength) {
                    return validationMessages.min(fieldName, minLength);
                }
            }

            // Regla: max
            if (ruleName === 'max' && value) {
                const maxLength = parseInt(params[0]);
                if (value.length > maxLength) {
                    return validationMessages.max(fieldName, maxLength);
                }
            }

            // Regla: numeric
            if (ruleName === 'numeric' && value) {
                if (isNaN(value)) {
                    return validationMessages.numeric(fieldName);
                }
            }

            // Regla: alpha
            if (ruleName === 'alpha' && value) {
                const alphaRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
                if (!alphaRegex.test(value)) {
                    return validationMessages.alpha(fieldName);
                }
            }

            // Regla: alpha_num
            if (ruleName === 'alpha_num' && value) {
                const alphaNumRegex = /^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ]+$/;
                if (!alphaNumRegex.test(value)) {
                    return validationMessages.alpha_num(fieldName);
                }
            }

            // Regla: integer
            if (ruleName === 'integer' && value) {
                if (!Number.isInteger(Number(value))) {
                    return validationMessages.integer(fieldName);
                }
            }

            // Regla: url
            if (ruleName === 'url' && value) {
                try {
                    new URL(value);
                } catch {
                    return validationMessages.url(fieldName);
                }
            }

            // Regla: regex
            if (ruleName === 'regex' && value) {
                const pattern = new RegExp(params[0]);
                if (!pattern.test(value)) {
                    return validationMessages.regex(fieldName);
                }
            }
        }

        return null;
    };

    const validate = (data, rules) => {
        clearErrors();
        let isValid = true;

        for (const [field, fieldRules] of Object.entries(rules)) {
            const value = data[field];
            const error = validateField(field, value, fieldRules);
            
            if (error) {
                errors[field] = error;
                isValid = false;
            }
        }

        return isValid;
    };

    const validateConfirmed = (data, field, confirmationField) => {
        const fieldName = attributeNames[field] || field;
        if (data[field] !== data[confirmationField]) {
            errors[confirmationField] = validationMessages.confirmed(fieldName);
            return false;
        }
        return true;
    };

    const hasError = (field) => {
        return !!errors[field];
    };

    const getError = (field) => {
        return errors[field] || '';
    };

    const clearError = (field) => {
        delete errors[field];
    };

    const clearErrors = () => {
        Object.keys(errors).forEach(key => delete errors[key]);
    };

    const setError = (field, message) => {
        errors[field] = message;
    };

    const setErrors = (serverErrors) => {
        clearErrors();
        Object.entries(serverErrors).forEach(([field, messages]) => {
            errors[field] = Array.isArray(messages) ? messages[0] : messages;
        });
    };

    return {
        errors,
        validate,
        validateConfirmed,
        hasError,
        getError,
        clearError,
        clearErrors,
        setError,
        setErrors,
    };
}
