// Keep what a person was typing when they switch language.
//
// Switching posts a form and reloads the page in the other language. Before
// that, the values of Livewire-bound fields on this page are written to
// sessionStorage; after the reload they are put back and Livewire is told.
// Passwords, files and hidden fields are never stored.

const storageKey = () => `campuly:drafts:${window.location.pathname}`;
const skippedTypes = ["password", "file", "hidden", "submit", "button"];

function modelName(element) {
    for (const attribute of element.attributes) {
        if (attribute.name.startsWith("wire:model")) {
            return attribute.value;
        }
    }

    return null;
}

function draftFields() {
    return [...document.querySelectorAll("input, textarea, select")].filter(
        (element) => modelName(element) !== null && !skippedTypes.includes(element.type),
    );
}

export function saveDrafts() {
    const drafts = draftFields().map((element) => ({
        model: modelName(element),
        value: element.type === "checkbox" || element.type === "radio" ? element.checked : element.value,
        radioValue: element.type === "radio" ? element.value : null,
    }));

    if (drafts.length > 0) {
        window.sessionStorage.setItem(storageKey(), JSON.stringify({ savedAt: Date.now(), drafts }));
    }
}

export function restoreDrafts() {
    const raw = window.sessionStorage.getItem(storageKey());

    if (raw === null) {
        return;
    }

    window.sessionStorage.removeItem(storageKey());

    let saved;

    try {
        saved = JSON.parse(raw);
    } catch {
        return;
    }

    // A draft older than ten minutes belongs to another visit.
    if (!saved?.drafts || Date.now() - saved.savedAt > 10 * 60 * 1000) {
        return;
    }

    for (const draft of saved.drafts) {
        const fields = draftFields().filter((element) => modelName(element) === draft.model);

        for (const element of fields) {
            if (element.type === "radio") {
                element.checked = draft.radioValue === element.value && draft.value === true;
            } else if (element.type === "checkbox") {
                element.checked = draft.value === true;
            } else {
                element.value = draft.value;
            }

            element.dispatchEvent(new Event("input", { bubbles: true }));
            element.dispatchEvent(new Event("change", { bubbles: true }));
        }
    }
}

window.campulySaveDrafts = saveDrafts;
document.addEventListener("livewire:navigated", restoreDrafts);
document.addEventListener("livewire:initialized", restoreDrafts);
