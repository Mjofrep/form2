const setNamesFromDataset = (scope) => {
    scope.querySelectorAll('[data-name]').forEach((input) => {
        input.name = input.dataset.name;
    });
};

const syncQuestionVisibility = (item) => {
    const select = item.querySelector('[data-question-type]');

    if (!select) {
        return;
    }

    const type = select.value;
    const optionBox = item.querySelector('.option-box');
    const placeholderBox = item.querySelector('.placeholder-box');

    if (optionBox) {
        optionBox.classList.toggle('d-none', !['single_choice', 'multiple_choice'].includes(type));
    }

    if (placeholderBox) {
        placeholderBox.classList.toggle('d-none', ['single_choice', 'multiple_choice', 'true_false', 'file', 'date'].includes(type));
    }
};

const syncSupportVisibility = (item) => {
    const select = item.querySelector('[data-support-type]');

    if (!select) {
        return;
    }

    const type = select.value;
    const textBox = item.querySelector('.support-text-box');
    const urlBox = item.querySelector('.support-url-box');

    textBox?.classList.toggle('d-none', type !== 'text');
    urlBox?.classList.toggle('d-none', type === 'text');

    textBox?.querySelectorAll('input, textarea, select').forEach((input) => {
        input.disabled = type !== 'text';
    });

    urlBox?.querySelectorAll('input, textarea, select').forEach((input) => {
        input.disabled = type === 'text';
    });
};

const syncBlockVisibility = (item) => {
    const select = item.querySelector('[data-block-type]');

    if (!select) {
        return;
    }

    const isImage = select.value === 'image';
    item.querySelector('.block-text-box')?.classList.toggle('d-none', isImage);
    item.querySelector('.block-image-box')?.classList.toggle('d-none', !isImage);
};

const updateRepeaterIndexes = (wrapper) => {
    wrapper.querySelectorAll('[data-repeater-item]').forEach((item, index) => {
        item.querySelectorAll('[data-template-name]').forEach((input) => {
            input.dataset.name = input.dataset.templateName.replaceAll('__INDEX__', index);
        });

        setNamesFromDataset(item);
        syncQuestionVisibility(item);
        syncSupportVisibility(item);
        syncBlockVisibility(item);
    });
};

const bootRepeaters = () => {
    document.querySelectorAll('[data-repeater]').forEach((repeater) => {
        const list = repeater.querySelector('[data-repeater-list]');
        const template = repeater.querySelector('template');

        repeater.querySelector('[data-repeater-add]')?.addEventListener('click', () => {
            if (!list || !template) {
                return;
            }

            list.insertAdjacentHTML('beforeend', template.innerHTML);
            updateRepeaterIndexes(list);
        });

        repeater.addEventListener('click', (event) => {
            const remove = event.target.closest('[data-repeater-remove]');

            if (!remove) {
                return;
            }

            remove.closest('[data-repeater-item]')?.remove();
            if (list) {
                updateRepeaterIndexes(list);
            }
        });

        repeater.addEventListener('change', (event) => {
            const item = event.target.closest('[data-repeater-item]');

            if (!item) {
                return;
            }

            syncQuestionVisibility(item);
            syncSupportVisibility(item);
            syncBlockVisibility(item);
        });

        if (list) {
            updateRepeaterIndexes(list);
        }
    });
};

const bootQrCodes = () => {
    document.querySelectorAll('[data-qr-url]').forEach((node) => {
        const url = node.dataset.qrUrl;

        if (!url || node.dataset.qrLoaded === '1') {
            return;
        }

        new QRCode(node, {
            text: url,
            width: 180,
            height: 180,
        });

        node.dataset.qrLoaded = '1';
    });
};

document.addEventListener('DOMContentLoaded', () => {
    bootRepeaters();
    bootQrCodes();
});
