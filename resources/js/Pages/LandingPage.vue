<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { useTheme } from '@/composables/useTheme';
import busImage from '../../images/bus.jpg';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const page = usePage();
const {
    initializeTheme,
    themeClasses,
} = useTheme();

onMounted(() => {
    // Inicializar tema con el timeMode del servidor
    const serverTimeMode = page.props.timeMode;
    initializeTheme(null, serverTimeMode);
});
</script>

<template>

    <Head title="TransPorta - Pasajes y Encomiendas" />

    <div :class="themeClasses" class="min-h-screen" style="background-color: var(--bg-primary);">
        <!-- Navigation Bar Mejorado -->
        <nav class="fixed top-0 left-0 w-full z-50 shadow-sm border-b" style="
        background-color: var(--bg-secondary);
        border-color: var(--border-primary);
        backdrop-filter: blur(12px);
    ">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">

                    <!-- Logo -->
                    <div class="flex items-center gap-2">
                        <span class="text-2xl" aria-hidden="true">🚌</span>
                        <span class="font-bold text-lg" style="color: var(--primary-600);">
                            TransPorta
                        </span>
                    </div>

                    <!-- Links -->
                    <div class="flex items-center gap-8">

                        <a href="#hero" class="nav-link">
                            Inicio
                        </a>

                        <a href="#servicios" class="nav-link">
                            Servicios
                        </a>

                        <a href="#rutas" class="nav-link">
                            Rutas
                        </a>

                        <a href="#contacto" class="nav-link">
                            Contacto
                        </a>

                        <!-- Botón Iniciar Sesión -->
                        <Link v-if="canLogin" :href="route('login')"
                            class="px-4 py-2 rounded-lg font-medium border transition-all" style="
                        color: var(--primary-600);
                        border-color: var(--primary-600);
                    ">
                        Iniciar Sesión
                        </Link>

                        <!-- Botón Registrarse -->
                        <Link v-if="canRegister" :href="route('register')"
                            class="px-4 py-2 rounded-lg font-medium text-white transition-all"
                            style="background-color: var(--primary-600);">
                        Registrarse
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
       <section id="hero" class="pb-24 px-4 sm:px-6 lg:px-8" style="padding-top: calc(64px + 3rem);">

            <div class="max-w-7xl mx-auto">
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <!-- Columna texto -->
                    <div class="space-y-8 text-center md:text-left">
                        <div class="inline-flex items-center gap-2 px-4 py-1 rounded-full text-xs font-semibold tracking-wide"
                            style="
                        background-color: var(--bg-secondary);
                        color: var(--primary-600);
                        border: 1px solid var(--border-primary);
                    ">
                            <span>🚌</span>
                            <span>Pasajes y Encomiendas · Santa Cruz - Bolivia</span>
                        </div>

                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight"
                            style="color: var(--text-primary);">
                            Adquiere tus
                            <span style="color: var(--primary-600);"> Pasajes </span>
                            y
                            <span style="color: var(--secondary-600);"> Encomiendas </span>
                            al mejor precio
                        </h1>

                        <p class="text-lg md:text-xl max-w-xl mx-auto md:mx-0" style="color: var(--text-secondary);">
                            Viaja seguro y cómodo con TransPorta. Conectamos Santa Cruz con los
                            mejores destinos de Bolivia, las 24 horas del día.
                        </p>

                        <div class="flex flex-wrap justify-center md:justify-start gap-4">
                            <Link v-if="canRegister" :href="route('register')"
                                class="px-8 py-3 rounded-lg font-semibold text-white shadow-lg hero-cta-primary"
                                style="background-color: var(--primary-600);">
                            Comprar Ahora
                            </Link>

                            <a href="#servicios" class="px-8 py-3 rounded-lg font-semibold hero-cta-secondary" style="
                            background-color: var(--bg-secondary);
                            color: var(--text-primary);
                            border: 1px solid var(--border-primary);
                        ">
                                Ver Servicios
                            </a>
                        </div>

                        <div class="grid grid-cols-3 gap-4 pt-6">
                            <div class="hero-stat-card">
                                <div class="text-3xl font-bold" style="color: var(--primary-600);">10+</div>
                                <div class="text-sm mt-1" style="color: var(--text-tertiary);">Rutas</div>
                            </div>
                            <div class="hero-stat-card">
                                <div class="text-3xl font-bold" style="color: var(--secondary-600);">24/7</div>
                                <div class="text-sm mt-1" style="color: var(--text-tertiary);">Servicio</div>
                            </div>
                            <div class="hero-stat-card">
                                <div class="text-3xl font-bold" style="color: var(--accent-600);">100%</div>
                                <div class="text-sm mt-1" style="color: var(--text-tertiary);">Seguro</div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna imagen -->
                    <div class="relative flex justify-center md:justify-end">
                        <div class="hero-bus-card">
                            <img :src="busImage" alt="Bus TransPorta" class="w-full h-full object-cover rounded-2xl" />

                            <div class="absolute -bottom-4 -left-4 px-4 py-3 rounded-xl text-sm shadow-md hidden sm:flex flex-col"
                                style="
                            background-color: var(--bg-primary);
                            border: 1px solid var(--border-primary);
                            color: var(--text-primary);
                        ">
                                <span class="font-semibold">Salidas diarias</span>
                                <span class="text-xs" style="color: var(--text-tertiary);">
                                    Desde Santa Cruz y Comarapa
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>



        <!-- Routes Section -->
        <section id="rutas" class="py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4" style="color: var(--text-primary);">
                        Rutas Disponibles
                    </h2>
                    <p class="text-lg" style="color: var(--text-secondary);">
                        Conectamos las principales ciudades de Bolivia
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-6">
                    <!-- Ruta 1 -->
                    <div class="rounded-xl p-6 shadow-lg"
                        style="background-color: var(--bg-secondary); border-left: 4px solid var(--primary-600);">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <div class="font-bold text-lg" style="color: var(--text-primary);">Santa Cruz</div>
                                <div class="text-sm" style="color: var(--text-tertiary);">Terminal Bimodal</div>
                            </div>
                            <div style="color: var(--primary-600);">→</div>
                            <div class="text-right">
                                <div class="font-bold text-lg" style="color: var(--text-primary);">Comarapa</div>
                                <div class="text-sm" style="color: var(--text-tertiary);">Terminal</div>
                            </div>
                        </div>
                        <div class="text-sm" style="color: var(--text-secondary);">
                            <div>⏱️ Duración: 3.5 horas</div>
                            <div>💰 Desde: Bs. 30</div>
                        </div>
                    </div>

                    <!-- Ruta 2 -->
                    <div class="rounded-xl p-6 shadow-lg"
                        style="background-color: var(--bg-secondary); border-left: 4px solid var(--secondary-600);">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <div class="font-bold text-lg" style="color: var(--text-primary);">Comarapa</div>
                                <div class="text-sm" style="color: var(--text-tertiary);">Terminal</div>
                            </div>
                            <div style="color: var(--secondary-600);">→</div>
                            <div class="text-right">
                                <div class="font-bold text-lg" style="color: var(--text-primary);">La Paz</div>
                                <div class="text-sm" style="color: var(--text-tertiary);">Terminal</div>
                            </div>
                        </div>
                        <div class="text-sm" style="color: var(--text-secondary);">
                            <div>⏱️ Duración: 8 horas</div>
                            <div>💰 Desde: Bs. 80</div>
                        </div>
                    </div>

                    <!-- Ruta 3 -->
                    <div class="rounded-xl p-6 shadow-lg"
                        style="background-color: var(--bg-secondary); border-left: 4px solid var(--accent-600);">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <div class="font-bold text-lg" style="color: var(--text-primary);">Santa Cruz</div>
                                <div class="text-sm" style="color: var(--text-tertiary);">Terminal Bimodal</div>
                            </div>
                            <div style="color: var(--accent-600);">→</div>
                            <div class="text-right">
                                <div class="font-bold text-lg" style="color: var(--text-primary);">Cochabamba</div>
                                <div class="text-sm" style="color: var(--text-tertiary);">Terminal</div>
                            </div>
                        </div>
                        <div class="text-sm" style="color: var(--text-secondary);">
                            <div>⏱️ Duración: 6 horas</div>
                            <div>💰 Desde: Bs. 60</div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-12">
                    <Link v-if="canRegister" :href="route('register')"
                        class="inline-block px-8 py-3 rounded-lg font-semibold text-white transition-all hover:opacity-90 shadow-lg"
                        style="background-color: var(--primary-600);">
                    Ver Todas las Rutas
                    </Link>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contacto" class="py-20 px-4 sm:px-6 lg:px-8" style="background-color: var(--bg-secondary);">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4" style="color: var(--text-primary);">
                        Contáctanos
                    </h2>
                    <p class="text-lg" style="color: var(--text-secondary);">
                        Estamos aquí para ayudarte
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Teléfono -->
                    <div class="text-center p-6 rounded-xl" style="background-color: var(--bg-primary);">
                        <div class="text-4xl mb-4">📞</div>
                        <h3 class="font-bold text-lg mb-2" style="color: var(--text-primary);">Teléfono</h3>
                        <p style="color: var(--text-secondary);">71231233</p>
                        <p class="text-sm mt-2" style="color: var(--text-tertiary);">Lun - Dom: 24/7</p>
                    </div>

                    <!-- Ubicación -->
                    <div class="text-center p-6 rounded-xl" style="background-color: var(--bg-primary);">
                        <div class="text-4xl mb-4">📍</div>
                        <h3 class="font-bold text-lg mb-2" style="color: var(--text-primary);">Ubicación</h3>
                        <p style="color: var(--text-secondary);">Santa Cruz, Bolivia</p>
                        <p class="text-sm mt-2" style="color: var(--text-tertiary);">Terminal Bimodal</p>
                    </div>

                    <!-- WhatsApp -->
                    <div class="text-center p-6 rounded-xl" style="background-color: var(--bg-primary);">
                        <div class="text-4xl mb-4">💬</div>
                        <h3 class="font-bold text-lg mb-2" style="color: var(--text-primary);">WhatsApp</h3>
                        <a href="https://wa.me/59171231233" target="_blank"
                            class="inline-block px-6 py-2 rounded-lg transition-all hover:opacity-90 text-white mt-2"
                            style="background-color: #25D366;">
                            Enviar Mensaje
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-8 px-4 sm:px-6 lg:px-8 border-t"
            style="background-color: var(--bg-primary); border-color: var(--border-primary);">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="mb-4 md:mb-0">
                        <div class="flex items-center space-x-2">
                            <span class="text-2xl">🚌</span>
                            <span class="font-bold" style="color: var(--primary-600);">TransPorta</span>
                        </div>
                        <p class="text-sm mt-1" style="color: var(--text-tertiary);">
                            © 2025 TransPorta. Todos los derechos reservados.
                        </p>
                    </div>
                    <div class="flex space-x-6">
                        <a href="#" class="transition-colors hover:opacity-80"
                            style="color: var(--text-secondary);">Términos</a>
                        <a href="#" class="transition-colors hover:opacity-80"
                            style="color: var(--text-secondary);">Privacidad</a>
                        <a href="#contacto" class="transition-colors hover:opacity-80"
                            style="color: var(--text-secondary);">Soporte</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.hero-cta-primary {
    transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
}

.hero-cta-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 18px 40px rgba(0, 0, 0, 0.18);
    opacity: 0.95;
}

.hero-cta-secondary {
    transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

.hero-cta-secondary:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
}

.hero-stat-card {
    padding: 0.9rem 0.5rem;
    border-radius: 0.9rem;
    border: 1px solid var(--border-primary);
    background-color: var(--bg-primary);
    transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

.hero-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 25px rgba(0, 0, 0, 0.12);
    background-color: var(--bg-secondary);
}

.hero-bus-card {
    position: relative;
    width: 100%;
    max-width: 380px;
    aspect-ratio: 4 / 5;
    border-radius: 1.5rem;
    padding: 3rem;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35);
    background: linear-gradient(135deg,
            var(--primary-500),
            var(--secondary-500));
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hero-bus-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 35px 80px rgba(0, 0, 0, 0.4);
}
</style>
