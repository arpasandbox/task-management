import Sortable from 'sortablejs';

const sortableInstances = new Map();

function destroyKanbanSortables() {
    sortableInstances.forEach((instance) => instance.destroy());
    sortableInstances.clear();
}

function resolveLivewireComponent(board) {
    const componentId = board.dataset.livewireId;

    if (componentId && window.Livewire) {
        const component = Livewire.find(componentId);

        if (component) {
            return component;
        }
    }

    const wireRoot = board.closest('[wire\\:id]');

    if (wireRoot && window.Livewire) {
        return Livewire.find(wireRoot.getAttribute('wire:id'));
    }

    return null;
}

function initKanbanSortables() {
    const board = document.querySelector('[data-kanban-board]');

    if (!board) {
        destroyKanbanSortables();

        return;
    }

    destroyKanbanSortables();

    board.querySelectorAll('[data-kanban-column]').forEach((column) => {
        const statusId = column.dataset.statusId;

        if (! statusId || sortableInstances.has(statusId)) {
            return;
        }

        const instance = Sortable.create(column, {
            group: 'kanban',
            animation: 150,
            delay: 120,
            delayOnTouchOnly: true,
            draggable: '[data-task-id]',
            ghostClass: 'kanban-ghost',
            chosenClass: 'kanban-chosen',
            dragClass: 'kanban-drag',
            onEnd(event) {
                if (event.from === event.to && event.oldIndex === event.newIndex) {
                    return;
                }

                const taskId = event.item.dataset.taskId;
                const newStatusId = event.to.dataset.statusId;
                const position = event.newIndex + 1;

                const component = resolveLivewireComponent(board);

                if (! component || ! taskId || ! newStatusId) {
                    initKanbanSortables();

                    return;
                }

                component.call('moveTask', Number(taskId), Number(newStatusId), position);
            },
        });

        sortableInstances.set(statusId, instance);
    });
}

document.addEventListener('livewire:init', () => {
    initKanbanSortables();

    Livewire.hook('morph.updated', ({ el }) => {
        if (el.matches?.('[data-kanban-board]') || el.querySelector?.('[data-kanban-board]')) {
            initKanbanSortables();
        }
    });
});

document.addEventListener('livewire:navigated', initKanbanSortables);
document.addEventListener('DOMContentLoaded', initKanbanSortables);
