export const PAISES = [
    { codigo: '+93', nombre: 'Afganistán', iso: 'AF' },
    { codigo: '+355', nombre: 'Albania', iso: 'AL' },
    { codigo: '+49', nombre: 'Alemania', iso: 'DE' },
    { codigo: '+376', nombre: 'Andorra', iso: 'AD' },
    { codigo: '+54', nombre: 'Argentina', iso: 'AR' },
    { codigo: '+61', nombre: 'Australia', iso: 'AU' },
    { codigo: '+43', nombre: 'Austria', iso: 'AT' },
    { codigo: '+32', nombre: 'Bélgica', iso: 'BE' },
    { codigo: '+591', nombre: 'Bolivia', iso: 'BO' },
    { codigo: '+55', nombre: 'Brasil', iso: 'BR' },
    { codigo: '+1', nombre: 'Canadá', iso: 'CA' },
    { codigo: '+56', nombre: 'Chile', iso: 'CL' },
    { codigo: '+86', nombre: 'China', iso: 'CN' },
    { codigo: '+57', nombre: 'Colombia', iso: 'CO' },
    { codigo: '+82', nombre: 'Corea del Sur', iso: 'KR' },
    { codigo: '+506', nombre: 'Costa Rica', iso: 'CR' },
    { codigo: '+53', nombre: 'Cuba', iso: 'CU' },
    { codigo: '+45', nombre: 'Dinamarca', iso: 'DK' },
    { codigo: '+593', nombre: 'Ecuador', iso: 'EC' },
    { codigo: '+503', nombre: 'El Salvador', iso: 'SV' },
    { codigo: '+34', nombre: 'España', iso: 'ES' },
    { codigo: '+1', nombre: 'Estados Unidos', iso: 'US' },
    { codigo: '+33', nombre: 'Francia', iso: 'FR' },
    { codigo: '+502', nombre: 'Guatemala', iso: 'GT' },
    { codigo: '+504', nombre: 'Honduras', iso: 'HN' },
    { codigo: '+91', nombre: 'India', iso: 'IN' },
    { codigo: '+39', nombre: 'Italia', iso: 'IT' },
    { codigo: '+81', nombre: 'Japón', iso: 'JP' },
    { codigo: '+52', nombre: 'México', iso: 'MX' },
    { codigo: '+505', nombre: 'Nicaragua', iso: 'NI' },
    { codigo: '+47', nombre: 'Noruega', iso: 'NO' },
    { codigo: '+507', nombre: 'Panamá', iso: 'PA' },
    { codigo: '+595', nombre: 'Paraguay', iso: 'PY' },
    { codigo: '+51', nombre: 'Perú', iso: 'PE' },
    { codigo: '+351', nombre: 'Portugal', iso: 'PT' },
    { codigo: '+1', nombre: 'Puerto Rico', iso: 'PR' },
    { codigo: '+44', nombre: 'Reino Unido', iso: 'GB' },
    { codigo: '+1', nombre: 'República Dominicana', iso: 'DO' },
    { codigo: '+40', nombre: 'Rumania', iso: 'RO' },
    { codigo: '+7', nombre: 'Rusia', iso: 'RU' },
    { codigo: '+46', nombre: 'Suecia', iso: 'SE' },
    { codigo: '+41', nombre: 'Suiza', iso: 'CH' },
    { codigo: '+598', nombre: 'Uruguay', iso: 'UY' },
    { codigo: '+58', nombre: 'Venezuela', iso: 'VE' },
];

export const getPaisPorCodigo = (codigo) => {
    return PAISES.find(p => p.codigo === codigo);
};

export const getPaisPorNombre = (nombre) => {
    return PAISES.find(p => p.nombre === nombre);
};
