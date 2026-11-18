import {ref} from "vue";

const theme = ref(document.documentElement.dataset.theme || "dark");

export function useTheme(){
    function set(value) {
        theme.value = value;
        document.documentElement.dataset.theme = value;
        try {
            localStorage.setItem("theme", value);
        } catch {
            // storage blocked: theme still work for this visit
        }
    }
    function toggle() {
        set(theme.value === "dark" ? "light" : "dark");
    }

    return { theme, toggle };
}