<script setup>
import { useTheme } from '@/composables/useTheme';
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import { 
    SunIcon, 
    MoonIcon, 
    SwatchIcon,
    CheckIcon
} from '@heroicons/vue/24/outline';

const { 
    currentTheme, 
    availableThemes, 
    isDarkMode, 
    isAutoMode,
    effectiveMode,
    setTheme, 
    toggleDarkMode,
    setAutoMode,
    isThemeActive 
} = useTheme();
</script>

<template>
    <div class="flex items-center gap-2">
        <!-- Selector de Tema -->
        <Menu as="div" class="relative inline-block text-left">
            <MenuButton
                class="inline-flex items-center justify-center rounded-md px-3 py-2 text-sm font-medium hover:bg-muted transition-colors"
                :class="effectiveMode === 'dark' ? 'text-foreground' : 'text-foreground'"
            >
                <SwatchIcon class="h-5 w-5" />
                <span class="ml-2 hidden sm:inline">{{ currentTheme === 'ninos' ? 'Niños' : currentTheme === 'jovenes' ? 'Jóvenes' : 'Adultos' }}</span>
            </MenuButton>

            <transition
                enter-active-class="transition ease-out duration-100"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
            >
                <MenuItems
                    class="absolute right-0 z-10 mt-2 w-64 origin-top-right rounded-md bg-card shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                >
                    <div class="p-2">
                        <div class="px-3 py-2 text-xs font-semibold text-muted-foreground uppercase tracking-wider border-b border-border mb-2">
                            Seleccionar Tema
                        </div>
                        <MenuItem
                            v-for="theme in availableThemes"
                            :key="theme.id"
                            v-slot="{ active }"
                        >
                            <button
                                @click="setTheme(theme.id)"
                                :class="[
                                    active ? 'bg-muted' : '',
                                    'group flex w-full items-center rounded-md px-3 py-2 text-sm transition-colors'
                                ]"
                            >
                                <span class="text-2xl mr-3">{{ theme.icon }}</span>
                                <div class="flex-1 text-left">
                                    <div class="font-medium text-foreground">{{ theme.name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ theme.description }}</div>
                                </div>
                                <CheckIcon 
                                    v-if="isThemeActive(theme.id)"
                                    class="h-5 w-5 text-primary"
                                />
                            </button>
                        </MenuItem>
                    </div>
                </MenuItems>
            </transition>
        </Menu>

        <!-- Toggle Dark Mode -->
        <button
            @click="toggleDarkMode"
            class="inline-flex items-center justify-center rounded-md px-3 py-2 text-sm font-medium hover:bg-muted transition-colors"
            :class="effectiveMode === 'dark' ? 'text-foreground' : 'text-foreground'"
            :title="isAutoMode ? 'Modo automático activo' : (isDarkMode ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro')"
        >
            <component 
                :is="effectiveMode === 'dark' ? MoonIcon : SunIcon" 
                class="h-5 w-5"
                :class="isAutoMode ? 'opacity-50' : ''"
            />
            <span v-if="isAutoMode" class="ml-1 text-xs opacity-50">Auto</span>
        </button>
    </div>
</template>
