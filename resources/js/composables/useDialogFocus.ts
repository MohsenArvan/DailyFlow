import { nextTick, onBeforeUnmount, watch, type Ref } from 'vue';

export function useDialogFocus(root: Ref<HTMLElement | undefined>, open: () => boolean) {
    let previous: HTMLElement | null = null;

    function focusable() {
        return [...(root.value?.querySelectorAll<HTMLElement>(
            'a[href], button, input, select, textarea, summary, [tabindex]',
        ) ?? [])].filter(element => !element.matches(':disabled') && element.tabIndex >= 0 && element.getClientRects().length > 0);
    }

    watch(open, async visible => {
        if (visible) {
            previous = document.activeElement instanceof HTMLElement ? document.activeElement : null;
            await nextTick();
            if (open()) (focusable()[0] ?? root.value)?.focus();
        } else {
            await nextTick();
            if (!open()) restore();
        }
    }, { immediate: true });

    function restore() {
        previous?.focus();
        previous = null;
    }

    onBeforeUnmount(restore);

    return function trap(event: KeyboardEvent) {
        if (event.key !== 'Tab') return;
        const elements = focusable();
        const first = elements[0] ?? root.value;
        const last = elements.at(-1) ?? root.value;
        const current = document.activeElement;
        if (!elements.includes(current as HTMLElement) || current === (event.shiftKey ? first : last)) {
            event.preventDefault();
            (event.shiftKey ? last : first)?.focus();
        }
    };
}
