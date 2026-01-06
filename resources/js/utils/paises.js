export const PAISES = [
    { codigo: '+93', nombre: 'Afganistán', iso: 'AF', moneda: 'USD', simbolo: '$' },
    { codigo: '+355', nombre: 'Albania', iso: 'AL', moneda: 'USD', simbolo: '$' },
    { codigo: '+49', nombre: 'Alemania', iso: 'DE', moneda: 'EUR', simbolo: '€' },
    { codigo: '+376', nombre: 'Andorra', iso: 'AD', moneda: 'EUR', simbolo: '€' },
    { codigo: '+54', nombre: 'Argentina', iso: 'AR', moneda: 'ARS', simbolo: '$' },
    { codigo: '+61', nombre: 'Australia', iso: 'AU', moneda: 'AUD', simbolo: '$' },
    { codigo: '+43', nombre: 'Austria', iso: 'AT', moneda: 'EUR', simbolo: '€' },
    { codigo: '+32', nombre: 'Bélgica', iso: 'BE', moneda: 'EUR', simbolo: '€' },
    { codigo: '+591', nombre: 'Bolivia', iso: 'BO', moneda: 'BOB', simbolo: 'Bs' },
    { codigo: '+55', nombre: 'Brasil', iso: 'BR', moneda: 'BRL', simbolo: 'R$' },
    { codigo: '+1', nombre: 'Canadá', iso: 'CA', moneda: 'CAD', simbolo: '$' },
    { codigo: '+56', nombre: 'Chile', iso: 'CL', moneda: 'CLP', simbolo: '$' },
    { codigo: '+86', nombre: 'China', iso: 'CN', moneda: 'CNY', simbolo: '¥' },
    { codigo: '+57', nombre: 'Colombia', iso: 'CO', moneda: 'COP', simbolo: '$' },
    { codigo: '+82', nombre: 'Corea del Sur', iso: 'KR', moneda: 'KRW', simbolo: '₩' },
    { codigo: '+506', nombre: 'Costa Rica', iso: 'CR', moneda: 'CRC', simbolo: '₡' },
    { codigo: '+53', nombre: 'Cuba', iso: 'CU', moneda: 'USD', simbolo: '$' },
    { codigo: '+45', nombre: 'Dinamarca', iso: 'DK', moneda: 'DKK', simbolo: 'kr' },
    { codigo: '+593', nombre: 'Ecuador', iso: 'EC', moneda: 'USD', simbolo: '$' },
    { codigo: '+503', nombre: 'El Salvador', iso: 'SV', moneda: 'USD', simbolo: '$' },
    { codigo: '+34', nombre: 'España', iso: 'ES', moneda: 'EUR', simbolo: '€' },
    { codigo: '+1', nombre: 'Estados Unidos', iso: 'US', moneda: 'USD', simbolo: '$' },
    { codigo: '+33', nombre: 'Francia', iso: 'FR', moneda: 'EUR', simbolo: '€' },
    { codigo: '+502', nombre: 'Guatemala', iso: 'GT', moneda: 'GTQ', simbolo: 'Q' },
    { codigo: '+504', nombre: 'Honduras', iso: 'HN', moneda: 'HNL', simbolo: 'L' },
    { codigo: '+91', nombre: 'India', iso: 'IN', moneda: 'INR', simbolo: '₹' },
    { codigo: '+39', nombre: 'Italia', iso: 'IT', moneda: 'EUR', simbolo: '€' },
    { codigo: '+81', nombre: 'Japón', iso: 'JP', moneda: 'JPY', simbolo: '¥' },
    { codigo: '+52', nombre: 'México', iso: 'MX', moneda: 'MXN', simbolo: '$' },
    { codigo: '+505', nombre: 'Nicaragua', iso: 'NI', moneda: 'NIO', simbolo: 'C$' },
    { codigo: '+47', nombre: 'Noruega', iso: 'NO', moneda: 'NOK', simbolo: 'kr' },
    { codigo: '+507', nombre: 'Panamá', iso: 'PA', moneda: 'USD', simbolo: '$' },
    { codigo: '+595', nombre: 'Paraguay', iso: 'PY', moneda: 'PYG', simbolo: '₲' },
    { codigo: '+51', nombre: 'Perú', iso: 'PE', moneda: 'PEN', simbolo: 'S/' },
    { codigo: '+351', nombre: 'Portugal', iso: 'PT', moneda: 'EUR', simbolo: '€' },
    { codigo: '+1', nombre: 'Puerto Rico', iso: 'PR', moneda: 'USD', simbolo: '$' },
    { codigo: '+44', nombre: 'Reino Unido', iso: 'GB', moneda: 'GBP', simbolo: '£' },
    { codigo: '+1', nombre: 'República Dominicana', iso: 'DO', moneda: 'DOP', simbolo: '$' },
    { codigo: '+40', nombre: 'Rumania', iso: 'RO', moneda: 'RON', simbolo: 'lei' },
    { codigo: '+7', nombre: 'Rusia', iso: 'RU', moneda: 'RUB', simbolo: '₽' },
    { codigo: '+46', nombre: 'Suecia', iso: 'SE', moneda: 'SEK', simbolo: 'kr' },
    { codigo: '+41', nombre: 'Suiza', iso: 'CH', moneda: 'CHF', simbolo: 'Fr' },
    { codigo: '+598', nombre: 'Uruguay', iso: 'UY', moneda: 'UYU', simbolo: '$' },
    { codigo: '+58', nombre: 'Venezuela', iso: 'VE', moneda: 'USD', simbolo: '$' },
];

export const getPaisPorCodigo = (codigo) => {
    return PAISES.find(p => p.codigo === codigo);
};

export const getPaisPorNombre = (nombre) => {
    return PAISES.find(p => p.nombre === nombre);
};

export const getMonedaPorPais = (nombrePais) => {
    const pais = getPaisPorNombre(nombrePais);
    return pais ? pais.moneda : 'USD'; // Default USD si no se encuentra
};

export const getSimboloMoneda = (nombrePais) => {
    const pais = getPaisPorNombre(nombrePais);
    return pais ? pais.simbolo : '$';
};
