import ModalForm from 'core_form/modalform';
import {get_string as getString} from 'core/str';
import {exception as displayException, deleteCancelPromise} from 'core/notification';
import {call as fetchMany} from 'core/ajax';

export const init = async() => {

    // Add listener for adding a new source.
    let addsources = document.getElementsByClassName('add');
    addsources.forEach(element => {
        element.addEventListener('click', async(e) => {
            showModal(e, element.dataset.id, element.dataset.table);
        });
    });

    // Add listener to edit sources.
    let editsources = document.getElementsByClassName('edit');
    editsources.forEach(element => {
        element.addEventListener('click', async(e) => {
            showModal(e, element.dataset.id, element.dataset.table);
        });
    });

    // Add listener to import xml files.
    let importxml = document.getElementById('c4l_import');
    importxml.addEventListener('click', async(e) => {
        importModal(e);
    });

    // Add listener to delete sources.
    let deletesources = document.getElementsByClassName('delete');
    deletesources.forEach(element => {
        element.addEventListener('click', async(e) => {
            deleteModal(e, element.dataset.id, element.dataset.title, element.dataset.table);
        });
    });

    // Add listener to select compcat to show corresponding items.
    let compcats = document.getElementsByClassName('compcat');
    compcats.forEach(element => {
        element.addEventListener('click', async(e) => {
            showItems(e, element.dataset.compcat);
        });
    });

    // Add listener to manage component flavor relation.
    let compflavor = document.getElementById('c4l_compflavor_button');
    compflavor.addEventListener('click', async(e) => {
        compflavorModal(e);

    // Add image and text to item setting click area.
    let enlargeItems = document.querySelectorAll(
        '.flavor .card-body > div, .component .card-body > div, .variant .card-body > div'
    );
    enlargeItems.forEach(element => {
        element.addEventListener('click', async(e) => {
            console.log(e.target.querySelector('a.edit'));
            let target = e.target.querySelector('a.edit');
            console.log(target);
            if (target) {
                target.querySelector('a.edit').click();
            } else {
                target = e.target.closest('.item');
                console.log(target.querySelector('a.edit'));
                target.querySelector('a.edit').click();
            }
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
    if (id == 0) {
        title = getString('additem', 'tiny_c4l');
    } else {
        title = getString('edititem', 'tiny_c4l');
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
 * Show dynamic form to import xml backups.
 * @param {*} e
 */
function importModal(e) {
    e.preventDefault();
    let title = getString('import', 'tiny_c4l');

    const modalForm = new ModalForm({
        // Load import form.
        formClass: "tiny_c4l\\form\\management_import_form",
        args: {},
        modalConfig: {title: title},
    });
    // Reload page after submit.
    modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, () => location.reload());

    modalForm.show();
}

/**
 * Load modal to edit icon urls.
 * @param {*} e
 */
function compflavorModal(e) {
    e.preventDefault();
    let title = getString('manage', 'tiny_c4l');

    const modalForm = new ModalForm({
        // Load import form.
        formClass: "tiny_c4l\\form\\management_comp_flavor_form",
        args: {},
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

/**
 * Show items after clicking a compcat.
 * @param {*} e
 * @param {*} compcat
 */
function showItems(e, compcat) {
    // But first hide all items.
    let itemsHide = document.querySelectorAll('.flavor, .component, .variant');
    itemsHide.forEach(element => {
        element.classList.add('hidden');
    });

    // Show component with compcat name and read the flavors.
    let itemsShow = document.getElementsByClassName(compcat);
    let usedFlavors = [];
    itemsShow.forEach(element => {
        element.classList.remove('hidden');
        // Get all flavors to show.
        let flavors = element.dataset.flavors.split(' ');
        for (let value of flavors) {
            if (!usedFlavors.includes(value) && value.length != 0) {
                usedFlavors.push(value);
            }
        }
    });

    // Show the flavors.
    let flavorstring = usedFlavors.map(item => `.${item}`).join(', ');
    if (flavorstring.length) {
        let flavorsShow = document.querySelectorAll(flavorstring);
        flavorsShow.forEach(element => {
            element.classList.remove('hidden');
        });
    }

    // Show add buttons.
    let addsShow = document.getElementsByClassName('addcontainer');
    addsShow.forEach(element => {
        element.classList.remove('hidden');
    });
}
