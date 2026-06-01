import './bootstrap';
import DataTable from 'datatables.net-dt';
import Swal from 'sweetalert2';
import TomSelect from 'tom-select';
import 'datatables.net-dt/css/dataTables.dataTables.css';
import 'sweetalert2/dist/sweetalert2.min.css';
import 'tom-select/dist/css/tom-select.css';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const dataTableRegistry = new Map();
let activeGuestStatusFilter = '';
let activeGuestCategoryFilter = '';

const tableStateStorageKey = (tableId) => `controlxv:${tableId}:return-state`;

const saveReturnTableState = (tableId) => {
    const table = dataTableRegistry.get(tableId);

    if (!table) {
        return;
    }

    const info = table.page.info();

    window.sessionStorage.setItem(tableStateStorageKey(tableId), JSON.stringify({
        page: info.page,
        length: info.length,
    }));
};

const restoreReturnTableState = (tableId) => {
    const table = dataTableRegistry.get(tableId);

    if (!table) {
        return;
    }

    const raw = window.sessionStorage.getItem(tableStateStorageKey(tableId));

    if (!raw) {
        return;
    }

    try {
        const state = JSON.parse(raw);
        const targetLength = Number(state.length);
        const targetPage = Number(state.page);

        if (Number.isFinite(targetLength) && targetLength > 0 && table.page.len() !== targetLength) {
            table.page.len(targetLength);
        }

        if (Number.isFinite(targetPage) && targetPage >= 0) {
            table.page(targetPage).draw(false);
        } else {
            table.draw(false);
        }
    } catch (_error) {
        // ignore malformed session data
    } finally {
        window.sessionStorage.removeItem(tableStateStorageKey(tableId));
    }
};

const initTomSelect = () => {
    document.querySelectorAll('select').forEach((element) => {
        if (element.tomselect || element.dataset.nativeSelect !== undefined) {
            return;
        }

        new TomSelect(element, {
            create: false,
            allowEmptyOption: true,
            maxOptions: 500,
            searchField: ['text'],
            sortField: [
                { field: 'text', direction: 'asc' },
            ],
            render: {
                no_results: function (data, escape) {
                    return `<div class="no-results">Sin resultados para "${escape(data.input)}"</div>`;
                },
            },
        });
    });
};

document.addEventListener('DOMContentLoaded', initTomSelect);

const normalizeToneValue = (value) => value
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');

const applyInlineTone = (element) => {
    const group = element.dataset.inlineTone;

    if (!group) {
        return;
    }

    [...element.classList]
        .filter((className) => className.startsWith(`tone-${group}-`))
        .forEach((className) => element.classList.remove(className));

    const normalizedValue = normalizeToneValue(element.value || 'default') || 'default';
    element.classList.add(`tone-${group}-${normalizedValue}`);
};

const initInlineToneSelects = () => {
    document.querySelectorAll('select[data-inline-tone]').forEach((element) => {
        applyInlineTone(element);
    });
};

const initConfirmActions = () => {
    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || form.dataset.confirmBound === '1') {
            return;
        }

        const title = form.dataset.confirmTitle;

        if (!title) {
            return;
        }

        event.preventDefault();

        Swal.fire({
            title,
            text: form.dataset.confirmText || 'Esta acción no se puede deshacer.',
            icon: form.dataset.confirmIcon || 'warning',
            showCancelButton: true,
            confirmButtonText: form.dataset.confirmButton || 'Sí, continuar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: form.dataset.confirmColor || '#8f55be',
            cancelButtonColor: '#b39abf',
            reverseButtons: true,
            focusCancel: true,
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            form.dataset.confirmBound = '1';
            form.requestSubmit();
            delete form.dataset.confirmBound;
        });
    });
};

const initFlashAlerts = () => {
    const flashElement = document.querySelector('[data-flash-status]');

    if (!flashElement) {
        return;
    }

    const message = flashElement.dataset.flashStatus?.trim();

    if (!message) {
        return;
    }

    Swal.fire({
        title: 'Cambios guardados',
        text: message,
        icon: 'success',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#8f55be',
    });
};

const showSuccessToast = (message) => {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: message,
        showConfirmButton: false,
        timer: 1800,
        timerProgressBar: true,
    });
};

const showErrorAlert = (message) => {
    Swal.fire({
        title: 'No se pudo guardar',
        text: message,
        icon: 'error',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#8f55be',
    });
};

const initCopyButtons = () => {
    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-copy-text]');

        if (!button) {
            return;
        }

        const text = button.dataset.copyText || '';

        if (text.trim() === '') {
            return;
        }

        try {
            await navigator.clipboard.writeText(text);
            showSuccessToast('Link copiado');
        } catch (error) {
            showErrorAlert('No se pudo copiar el link automáticamente.');
        }
    });
};

const initReturnTableStatePersistence = () => {
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-preserve-table]');

        if (!trigger) {
            return;
        }

        saveReturnTableState(trigger.dataset.preserveTable);
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || !form.dataset.preserveTable) {
            return;
        }

        saveReturnTableState(form.dataset.preserveTable);
    });
};

const buildAssociatedFormData = (form) => {
    const formData = new FormData(form);

    document.querySelectorAll(`[form="${form.id}"]`).forEach((control) => {
        if (!(control instanceof HTMLInputElement || control instanceof HTMLSelectElement || control instanceof HTMLTextAreaElement)) {
            return;
        }

        if (!control.name || control.disabled) {
            return;
        }

        if ((control instanceof HTMLInputElement) && ((control.type === 'checkbox' || control.type === 'radio') && !control.checked)) {
            return;
        }

        formData.set(control.name, control.value);
    });

    return formData;
};

const primeAutoSaveControls = () => {
    document.querySelectorAll('[form]').forEach((control) => {
        if (!(control instanceof HTMLInputElement || control instanceof HTMLSelectElement || control instanceof HTMLTextAreaElement)) {
            return;
        }

        if (control.dataset.lastSavedValue === undefined) {
            control.dataset.lastSavedValue = control.value;
        }
    });
};

const updateStatusSummaryCards = (statusSummary) => {
    if (!statusSummary) {
        return;
    }

    const total = Number(document.querySelector('[data-status-summary]')?.dataset.summaryTotal || 0);

    Object.entries(statusSummary).forEach(([status, count]) => {
        const element = document.querySelector(`[data-status-key="${CSS.escape(status)}"]`);
        const percentElement = document.querySelector(`[data-status-percent-key="${CSS.escape(status)}"]`);

        if (!element) {
            return;
        }

        element.textContent = new Intl.NumberFormat('es-MX').format(count);

        if (percentElement) {
            percentElement.textContent = `${total > 0 ? ((count / total) * 100).toFixed(1) : '0.0'}%`;
        }
    });
};

const updateCategorySummaryCards = (categorySummary) => {
    if (!categorySummary) {
        return;
    }

    const total = Number(document.querySelector('[data-category-summary]')?.dataset.summaryTotal || 0);

    Object.entries(categorySummary).forEach(([category, totals]) => {
        const element = document.querySelector(`[data-category-key="${CSS.escape(category)}"]`);
        const netElement = document.querySelector(`[data-category-net-key="${CSS.escape(category)}"]`);
        const percentElement = document.querySelector(`[data-category-percent-key="${CSS.escape(category)}"]`);
        const netPercentElement = document.querySelector(`[data-category-net-percent-key="${CSS.escape(category)}"]`);

        if (!element) {
            return;
        }

        element.textContent = new Intl.NumberFormat('es-MX').format(totals.total ?? 0);

        if (netElement) {
            netElement.textContent = new Intl.NumberFormat('es-MX').format(totals.without_rejected ?? 0);
        }

        if (percentElement) {
            percentElement.textContent = `${total > 0 ? (((totals.total ?? 0) / total) * 100).toFixed(1) : '0.0'}%`;
        }

        if (netPercentElement) {
            netPercentElement.textContent = `${total > 0 ? (((totals.without_rejected ?? 0) / total) * 100).toFixed(1) : '0.0'}%`;
        }
    });
};

const setActiveStatusCard = (status) => {
    document.querySelectorAll('[data-status-card]').forEach((card) => {
        const isActive = status !== '' && card.dataset.statusValue === status;
        card.classList.toggle('is-active', isActive);
        card.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });
};

const setActiveCategoryCard = (category) => {
    document.querySelectorAll('[data-category-card]').forEach((card) => {
        const isActive = category !== '' && card.dataset.categoryValue === category;
        card.classList.toggle('is-active', isActive);
        card.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });
};

const submitAjaxForm = async (form, submitter = null) => {
        const originalDisabledState = submitter?.disabled ?? false;
        const originalContent = submitter?.innerHTML ?? '';
        const controls = [...document.querySelectorAll(`[form="${form.id}"]`)];

        if (form.dataset.ajaxBusy === '1') {
            return;
        }

        form.dataset.ajaxBusy = '1';

        if (submitter) {
            submitter.disabled = true;
            submitter.innerHTML = `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v4"/>
                    <path d="m16.24 7.76 2.83-2.83"/>
                    <path d="M18 12h4"/>
                    <path d="m16.24 16.24 2.83 2.83"/>
                    <path d="M12 18v4"/>
                    <path d="m4.93 19.07 2.83-2.83"/>
                    <path d="M2 12h4"/>
                    <path d="m4.93 4.93 2.83 2.83"/>
                </svg>
            `;
        }

        const payload = buildAssociatedFormData(form);

        controls.forEach((control) => {
            control.dataset.ajaxOriginalDisabled = control.disabled ? '1' : '0';
            control.disabled = true;
        });

        try {
            const response = await window.axios.post(form.action, payload, {
                headers: {
                    Accept: 'application/json',
                },
            });

            controls.forEach((control) => {
                if (control instanceof HTMLInputElement || control instanceof HTMLSelectElement || control instanceof HTMLTextAreaElement) {
                    control.dataset.lastSavedValue = control.value;
                }
            });

            const row = controls[0]?.closest('tr');
            if (row && response.data.guest?.status) {
                row.dataset.statusCurrent = response.data.guest.status;
            }
            if (row && response.data.guest?.category) {
                row.dataset.categoryCurrent = response.data.guest.category;
            }

            updateCategorySummaryCards(response.data.category_summary);
            updateStatusSummaryCards(response.data.status_summary);
            dataTableRegistry.get('guests-table')?.draw(false);
            showSuccessToast(response.data.message || 'Cambios guardados');
        } catch (error) {
            const errors = error.response?.data?.errors;
            const firstError = errors ? Object.values(errors).flat()[0] : null;
            showErrorAlert(firstError || error.response?.data?.message || 'Revisa los datos e intenta de nuevo.');
        } finally {
            if (submitter) {
                submitter.disabled = originalDisabledState;
                submitter.innerHTML = originalContent;
            }

            controls.forEach((control) => {
                control.disabled = control.dataset.ajaxOriginalDisabled === '1';
                delete control.dataset.ajaxOriginalDisabled;
            });

            delete form.dataset.ajaxBusy;
        }
};

const initAjaxForms = () => {
    document.addEventListener('submit', async (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || form.dataset.ajaxSubmit === undefined) {
            return;
        }

        event.preventDefault();

        const submitter = event.submitter instanceof HTMLButtonElement ? event.submitter : null;
        await submitAjaxForm(form, submitter);
    });
};

const initAutoSaveForms = () => {
    primeAutoSaveControls();

    document.addEventListener('change', async (event) => {
        const control = event.target;

        if (!(control instanceof HTMLSelectElement) || !control.closest('#guests-table')) {
            return;
        }

        const formId = control.getAttribute('form');
        const form = formId ? document.getElementById(formId) : null;

        if (!(form instanceof HTMLFormElement) || form.dataset.autosaveForm === undefined) {
            return;
        }

        if (control.dataset.lastSavedValue === control.value) {
            return;
        }

        await submitAjaxForm(form);
    });

    document.addEventListener('focusout', async (event) => {
        const control = event.target;

        if (!(control instanceof HTMLInputElement) || !control.closest('#guests-table')) {
            return;
        }

        const formId = control.getAttribute('form');
        const form = formId ? document.getElementById(formId) : null;

        if (!(form instanceof HTMLFormElement) || form.dataset.autosaveForm === undefined) {
            return;
        }

        if (control.dataset.lastSavedValue === control.value) {
            return;
        }

        await submitAjaxForm(form);
    });
};

const initDataTables = () => {
    if (!DataTable.ext.search.__guestStatusFilterBound) {
        DataTable.ext.search.push((settings, _searchData, dataIndex) => {
            if (settings.nTable?.id !== 'guests-table') {
                return true;
            }

            const rowNode = settings.aoData?.[dataIndex]?.nTr;

            if (!rowNode) {
                return true;
            }

            const statusPass = activeGuestStatusFilter === '' || rowNode.dataset.statusCurrent === activeGuestStatusFilter;
            const categoryPass = activeGuestCategoryFilter === '' || rowNode.dataset.categoryCurrent === activeGuestCategoryFilter;

            return statusPass && categoryPass;
        });

        DataTable.ext.search.__guestStatusFilterBound = true;
    }

    document.querySelectorAll('[data-datatable]').forEach((element) => {
        if (element.dataset.datatableReady === '1') {
            return;
        }

        const type = element.dataset.datatable;
        const nonOrderableTargets = type === 'guests' ? [8] : (type === 'companions' ? [5] : []);
        const nonSearchableTargets = type === 'guests' ? [8] : (type === 'companions' ? [5] : []);
        const emptyTable = type === 'guests'
            ? 'No hay familias o grupos registrados'
            : (type === 'companions' ? 'No hay invitados registrados' : 'No hay registros disponibles');

        const table = new DataTable(element, {
            stateSave: true,
            stateDuration: -1,
            pageLength: 10,
            lengthMenu: [25, 50, 100, 250, 500],
            order: [[0, 'asc'], [1, 'asc']],
            drawCallback: () => {
                initInlineToneSelects();
                primeAutoSaveControls();
            },
            language: {
                search: 'Buscar:',
                lengthMenu: 'Mostrar _MENU_ registros',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'Sin registros disponibles',
                emptyTable,
                zeroRecords: 'No se encontraron coincidencias',
                paginate: {
                    first: 'Primero',
                    last: 'Último',
                    next: 'Siguiente',
                    previous: 'Anterior',
                },
            },
            columnDefs: [
                { orderable: false, targets: nonOrderableTargets },
                { searchable: false, targets: nonSearchableTargets },
            ],
            stateSaveParams: (_settings, data) => {
                data.activeGuestStatusFilter = activeGuestStatusFilter;
                data.activeGuestCategoryFilter = activeGuestCategoryFilter;
            },
            stateLoadParams: (_settings, data) => {
                if (type === 'guests') {
                    activeGuestStatusFilter = data.activeGuestStatusFilter || '';
                    activeGuestCategoryFilter = data.activeGuestCategoryFilter || '';
                }
            },
        });

        dataTableRegistry.set(element.id || type, table);

        if (type === 'guests') {
            setActiveStatusCard(activeGuestStatusFilter);
            setActiveCategoryCard(activeGuestCategoryFilter);
        }

        restoreReturnTableState(element.id || type);

        element.dataset.datatableReady = '1';
    });
};

const initFilterAwareExports = () => {
    document.querySelectorAll('[data-filter-export]').forEach((link) => {
        if (link.dataset.filterExportBound === '1') {
            return;
        }

        link.dataset.filterExportBound = '1';

        link.addEventListener('click', (event) => {
            const formSelector = link.dataset.filterExport;
            const form = formSelector ? document.querySelector(formSelector) : null;

            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            event.preventDefault();

            const url = new URL(link.href, window.location.origin);
            const formData = new FormData(form);

            url.search = '';

            for (const [key, value] of formData.entries()) {
                if (typeof value === 'string' && value.trim() !== '') {
                    url.searchParams.set(key, value);
                }
            }

            if (link.dataset.datatableContext === 'guests') {
                if (activeGuestStatusFilter !== '') {
                    url.searchParams.set('status', activeGuestStatusFilter);
                }

                if (activeGuestCategoryFilter !== '') {
                    url.searchParams.set('category', activeGuestCategoryFilter);
                }
            }

            window.location.href = url.toString();
        });
    });
};

const initStatusSummaryFilters = () => {
    const table = dataTableRegistry.get('guests-table');

    if (!table) {
        return;
    }

    const applyFilter = (card) => {
        const status = card.dataset.statusValue || '';
        const isAlreadyActive = card.classList.contains('is-active');
        const nextStatus = isAlreadyActive ? '' : status;

        activeGuestStatusFilter = nextStatus;
        table.draw(false);
        setActiveStatusCard(nextStatus);
    };

    document.querySelectorAll('[data-status-card]').forEach((card) => {
        if (card.dataset.statusFilterBound === '1') {
            return;
        }

        card.dataset.statusFilterBound = '1';

        card.addEventListener('click', () => applyFilter(card));
        card.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            event.preventDefault();
            applyFilter(card);
        });
    });
};

const initCategorySummaryFilters = () => {
    const table = dataTableRegistry.get('guests-table');

    if (!table) {
        return;
    }

    const applyFilter = (card) => {
        const category = card.dataset.categoryValue || '';
        const isAlreadyActive = card.classList.contains('is-active');
        const nextCategory = isAlreadyActive ? '' : category;

        activeGuestCategoryFilter = nextCategory;
        table.draw(false);
        setActiveCategoryCard(nextCategory);
    };

    document.querySelectorAll('[data-category-card]').forEach((card) => {
        if (card.dataset.categoryFilterBound === '1') {
            return;
        }

        card.dataset.categoryFilterBound = '1';

        card.addEventListener('click', () => applyFilter(card));
        card.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            event.preventDefault();
            applyFilter(card);
        });
    });
};

document.addEventListener('DOMContentLoaded', initDataTables);
document.addEventListener('DOMContentLoaded', initConfirmActions);
document.addEventListener('DOMContentLoaded', initFlashAlerts);
document.addEventListener('DOMContentLoaded', initCopyButtons);
document.addEventListener('DOMContentLoaded', initReturnTableStatePersistence);
document.addEventListener('DOMContentLoaded', initInlineToneSelects);
document.addEventListener('DOMContentLoaded', initAjaxForms);
document.addEventListener('DOMContentLoaded', initAutoSaveForms);
document.addEventListener('DOMContentLoaded', primeAutoSaveControls);
document.addEventListener('DOMContentLoaded', initCategorySummaryFilters);
document.addEventListener('DOMContentLoaded', initStatusSummaryFilters);
document.addEventListener('DOMContentLoaded', initFilterAwareExports);
document.addEventListener('change', (event) => {
    const element = event.target;

    if (element instanceof HTMLSelectElement && element.matches('select[data-inline-tone]')) {
        applyInlineTone(element);
    }
});
