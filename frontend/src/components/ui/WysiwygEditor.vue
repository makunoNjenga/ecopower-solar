<template>
    <div class="wysiwyg-editor">
        <QuillEditor
            v-model:content="content"
            :options="editorOptions"
            :disabled="disabled"
            contentType="html"
            theme="snow"
            @update:content="handleUpdate"
        />
    </div>
</template>

<script setup>
import { ref, watch, computed } from "vue";
import { QuillEditor } from "@vueup/vue-quill";
import "@vueup/vue-quill/dist/vue-quill.snow.css";

const props = defineProps({
    modelValue: {
        type: String,
        default: "",
    },
    placeholder: {
        type: String,
        default: "Enter description...",
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    height: {
        type: Number,
        default: 300,
    },
    toolbar: {
        type: [String, Array],
        default: null,
    },
    plugins: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue", "change"]);

const content = ref(props.modelValue);

// Watch for external changes to modelValue
watch(
    () => props.modelValue,
    (newValue) => {
        if (newValue !== content.value) {
            content.value = newValue;
        }
    }
);

/**
 * Handle content update from Quill editor
 */
const handleUpdate = ({ html }) => {
    content.value = html;
    emit("update:modelValue", html);
    emit("change", html);
};

/**
 * Parse toolbar string to Quill format
 */
const parseToolbar = (toolbarString) => {
    if (!toolbarString) {
        return [
            [{ header: [1, 2, 3, 4, 5, 6, false] }],
            ["bold", "italic", "underline", "strike"],
            [{ align: [] }],
            [{ list: "ordered" }, { list: "bullet" }],
            ["link", "image"],
            ["clean"],
        ];
    }

    // Convert TinyMCE-style toolbar to Quill format
    const groups = [];
    const formats = toolbarString.split("|").map((group) => group.trim());

    formats.forEach((group) => {
        const items = group.split(" ").filter((item) => item);
        const quillGroup = [];

        items.forEach((item) => {
            switch (item) {
                case "formatselect":
                    quillGroup.push({ header: [1, 2, 3, 4, 5, 6, false] });
                    break;
                case "bold":
                case "italic":
                case "underline":
                case "strike":
                case "blockquote":
                case "code-block":
                case "link":
                case "image":
                case "clean":
                    quillGroup.push(item);
                    break;
                case "strikethrough":
                    quillGroup.push("strike");
                    break;
                case "alignleft":
                case "aligncenter":
                case "alignright":
                case "alignjustify":
                    if (
                        !quillGroup.find(
                            (i) => typeof i === "object" && i.align
                        )
                    ) {
                        quillGroup.push({ align: [] });
                    }
                    break;
                case "numlist":
                    quillGroup.push({ list: "ordered" });
                    break;
                case "bullist":
                    quillGroup.push({ list: "bullet" });
                    break;
                case "outdent":
                    quillGroup.push({ indent: "-1" });
                    break;
                case "indent":
                    quillGroup.push({ indent: "+1" });
                    break;
                case "undo":
                case "redo":
                case "removeformat":
                    // Quill doesn't have these in toolbar, handled via keyboard
                    break;
                default:
                    // Skip unknown items
                    break;
            }
        });

        if (quillGroup.length > 0) {
            groups.push(quillGroup);
        }
    });

    return groups.length > 0 ? groups : undefined;
};

const editorOptions = computed(() => ({
    modules: {
        toolbar: parseToolbar(props.toolbar),
    },
    placeholder: props.placeholder,
    readOnly: props.disabled,
}));
</script>

<style scoped>
.wysiwyg-editor {
    @apply w-full;
}

/* Custom styling for Quill editor */
.wysiwyg-editor :deep(.ql-toolbar) {
    border: 1px solid #d1d5db;
    border-radius: 0.5rem 0.5rem 0 0;
    background-color: #f9fafb;
    border-bottom: 1px solid #d1d5db;
}

.wysiwyg-editor :deep(.ql-container) {
    border: 1px solid #d1d5db;
    border-top: none;
    border-radius: 0 0 0.5rem 0.5rem;
    font-family: Inter, system-ui, sans-serif;
    font-size: 14px;
}

.wysiwyg-editor :deep(.ql-editor) {
    min-height: v-bind("height + 'px'");
    max-height: v-bind("(height * 2) + 'px'");
    color: #374151;
    line-height: 1.6;
}

.wysiwyg-editor :deep(.ql-editor.ql-blank::before) {
    color: #9ca3af;
    font-style: normal;
}

.wysiwyg-editor :deep(.ql-editor p) {
    margin: 0 0 1em 0;
}

.wysiwyg-editor :deep(.ql-editor h1),
.wysiwyg-editor :deep(.ql-editor h2),
.wysiwyg-editor :deep(.ql-editor h3),
.wysiwyg-editor :deep(.ql-editor h4),
.wysiwyg-editor :deep(.ql-editor h5),
.wysiwyg-editor :deep(.ql-editor h6) {
    font-family: Poppins, system-ui, sans-serif;
    font-weight: 600;
    margin: 1em 0 0.5em 0;
    color: #111827;
}

.wysiwyg-editor :deep(.ql-editor ul),
.wysiwyg-editor :deep(.ql-editor ol) {
    padding-left: 1.5em;
}

.wysiwyg-editor :deep(.ql-editor blockquote) {
    border-left: 4px solid #2e7d32;
    margin: 1em 0;
    padding-left: 1em;
    color: #6b7280;
}

.wysiwyg-editor :deep(.ql-editor a) {
    color: #2e7d32;
    text-decoration: underline;
}

.wysiwyg-editor :deep(.ql-editor img) {
    max-width: 100%;
    height: auto;
}

/* Focus state */
.wysiwyg-editor :deep(.ql-container:focus-within) {
    border-color: #2e7d32;
    box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1);
}

.wysiwyg-editor :deep(.ql-toolbar:has(+ .ql-container:focus-within)) {
    border-color: #2e7d32;
}

/* Toolbar button active/hover states */
.wysiwyg-editor :deep(.ql-toolbar button:hover),
.wysiwyg-editor :deep(.ql-toolbar button:focus) {
    color: #2e7d32;
}

.wysiwyg-editor :deep(.ql-toolbar button.ql-active) {
    color: #2e7d32;
}

.wysiwyg-editor :deep(.ql-toolbar .ql-stroke) {
    stroke: #374151;
}

.wysiwyg-editor :deep(.ql-toolbar button:hover .ql-stroke),
.wysiwyg-editor :deep(.ql-toolbar button:focus .ql-stroke),
.wysiwyg-editor :deep(.ql-toolbar button.ql-active .ql-stroke) {
    stroke: #2e7d32;
}

.wysiwyg-editor :deep(.ql-toolbar .ql-fill) {
    fill: #374151;
}

.wysiwyg-editor :deep(.ql-toolbar button:hover .ql-fill),
.wysiwyg-editor :deep(.ql-toolbar button:focus .ql-fill),
.wysiwyg-editor :deep(.ql-toolbar button.ql-active .ql-fill) {
    fill: #2e7d32;
}

/* Disabled state */
.wysiwyg-editor :deep(.ql-container.ql-disabled) {
    background-color: #f3f4f6;
    cursor: not-allowed;
}

.wysiwyg-editor :deep(.ql-toolbar.ql-disabled) {
    opacity: 0.6;
    pointer-events: none;
}
</style>
