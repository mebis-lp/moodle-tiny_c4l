import ModalForm from 'core_form/modalform';
import {get_string as getString} from 'core/str';
import {exception as displayException, deleteCancelPromise} from 'core/notification';
import {call as fetchMany} from 'core/ajax';

export const init = async() => {

    // Add listener for adding a new source.
    // let addsource = document.getElementById('addsource');
    // addsource.addEventListener('click', async(e) => {
    //     showModal(e, 0);
    // });

    // Add listener to edit sources.
    let editsources = document.getElementsByClassName('edit');
    editsources.forEach(element => {
        element.addEventListener('click', async(e) => {
            showModal(e, element.dataset.id, element.dataset.table);
        });
    });

    // Add listener to delete sources.
    let deletesources = document.getElementsByClassName('delete');
    deletesources.forEach(element => {
        element.addEventListener('click', async(e) => {
            deleteModal(e, element.dataset.id, element.dataset.title, element.dataset.table);
        });
    });
};

/**
 * Show dynamic form to add/edit a source.
 * @param {*} e
 * @param {*} id
 * @param {*} table
 */
function showModal(e, id, table) {
    e.preventDefault();
    let title;
    if (id === 0) {
        title = getString('addsource', 'repository_imagehub');
    } else {
        title = getString('editsource', 'repository_imagehub');
    }

    const modalForm = new ModalForm({
        // Set formclass, depending on component.
        formClass: "tiny_c4l\\form\\management_" + table + "_form",
        args: {
            id: id,
        },
        modalConfig: {title: title},
    });
    // Reload page after submit.
    modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, () => location.reload());

    modalForm.show();
}

/**
 * Show dynamic form to delete a source.
 * @param {*} e
 * @param {*} id
 * @param {*} title
 * @param {*} table
 */
function deleteModal(e, id, title, table) {
    e.preventDefault();

    deleteCancelPromise(
        getString('delete', 'tiny_c4l', title),
        getString('deletewarning', 'tiny_c4l'),
    ).then(async() => {
        if (id !== 0) {
            try {
                console.log(id);
                console.log(table);
                const deleted = await deleteItem(id, table);
                if (deleted) {
                    const link = document.querySelector('[data-table="' + table + '"][data-id="' + id + '"]');
                    if (link) {
                        const card = link.closest(".item");
                        card.remove();
                    }
                }
            } catch (error) {
                displayException(error);
            }
        }
        return;
    }).catch(() => {
        return;
    });
}

/**
 * Delete c4l items.
 * @param {*} id
 * @param {*} table
 * @returns {mixed}
 */
export const deleteItem = (
    id,
    table,
) => fetchMany(
    [{
    methodname: 'tiny_c4l_delete_item',
    args: {
        id,
        table,
}}])[0];
