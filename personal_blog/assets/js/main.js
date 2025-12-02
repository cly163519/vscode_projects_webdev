function setupMarkdownPreview() {
    const textarea = document.querySelector('#content');
    const preview = document.querySelector('#preview');
    if (!textarea || !preview || typeof marked === 'undefined' || typeof DOMPurify === 'undefined') return;

    const render = () => {
        const raw = textarea.value;
        const dirty = marked.parse(raw, { breaks: true, gfm: true });
        preview.innerHTML = DOMPurify.sanitize(dirty);
    };
    textarea.addEventListener('input', render);
    render();
}

function renderMarkdownBlocks() {
    const blocks = document.querySelectorAll('[data-markdown]');
    blocks.forEach(block => {
        const raw = block.textContent;
        const dirty = marked.parse(raw, { breaks: true, gfm: true });
        block.innerHTML = DOMPurify.sanitize(dirty);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    setupMarkdownPreview();
    renderMarkdownBlocks();
});