import type { ComputedRef, Ref } from 'vue';
import { computed, ref } from 'vue';
import type { Appearance, ResolvedAppearance } from '@/types';

export type { Appearance, ResolvedAppearance };

export type UseAppearanceReturn = {
    appearance: Ref<Appearance>;
    resolvedAppearance: ComputedRef<ResolvedAppearance>;
    updateAppearance: (value: Appearance) => void;
};

// CAMBIO 1: Eliminé "_value: Appearance" de los paréntesis
export function updateTheme(): void {
    if (typeof window === 'undefined') {
        return;
    }

    document.documentElement.classList.remove('dark');
}

export function initializeTheme(): void {
    if (typeof window === 'undefined') {
        return;
    }

    localStorage.setItem('appearance', 'light');
    document.cookie =
        'appearance=light;path=/;max-age=31536000;SameSite=Lax';

    updateTheme(); // CAMBIO: Aquí ya no necesita argumento
}

const appearance = ref<Appearance>('light');

export function useAppearance(): UseAppearanceReturn {
    const resolvedAppearance = computed<ResolvedAppearance>(() => 'light');

    // CAMBIO 2: Eliminé "_value: Appearance" de los paréntesis aquí también
    function updateAppearance() {
        initializeTheme();
        appearance.value = 'light';
    }

    return {
        appearance,
        resolvedAppearance,
        updateAppearance,
    };
}