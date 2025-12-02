//  Get HTML elements 
var list = document.querySelector('#task-list');
var input = document.querySelector('#task-input');
var addBtn = document.querySelector('#add-btn');

//  Create a task <li> element 
function createTaskElement(task) {
    var li = document.createElement('li');
    li.dataset.id = task.id;
    li.textContent = task.title;

    // If task is completed, add 'checked' class
    if (task.done == 1) {
        li.classList.add('checked');
    }

    // Create delete button
    var removeBtn = document.createElement('button');
    removeBtn.className = 'close';
    removeBtn.textContent = '×';
    li.appendChild(removeBtn);

    // Click li to toggle (except clicking the delete button)
    li.addEventListener('click', function(ev) {
        if (ev.target === removeBtn) {
            return;
        }
        toggleTask(task.id);
    });

    // Click delete button
    removeBtn.addEventListener('click', function() {
        deleteTask(task.id);
    });

    // Double-click to edit
    li.addEventListener('dblclick', function() {
        var oldTitle = task.title;

        // Create input box
        var editInput = document.createElement('input');
        editInput.type = 'text';
        editInput.value = oldTitle;
        editInput.style.width = '90%';
        editInput.style.fontSize = '20px';
        editInput.style.border = 'none';
        editInput.style.outline = 'none';

        // Replace text with input
        li.textContent = '';
        li.appendChild(editInput);
        editInput.focus();

        // Save function
        function save() {
            var newTitle = editInput.value.trim();

            if (newTitle && newTitle !== oldTitle) {
                task.title = newTitle;
                li.textContent = newTitle;
                li.appendChild(removeBtn);
                updateTask(task.id, newTitle);
            } else {
                li.textContent = oldTitle;
                li.appendChild(removeBtn);
            }
        }

        // When input loses focus, save
        editInput.addEventListener('blur', save);

        // When press Enter, trigger blur
        editInput.addEventListener('keydown', function(ev) {
            if (ev.key === 'Enter') {
                editInput.blur();
            }
        });
    });

    return li;
}

//  Load tasks from server 
function loadTasks(showAll) {
    fetch('list.php?action=list')
        .then(function(res) {
            return res.json();
        })
        .then(function(items) {
            // Clear the list
            list.innerHTML = '';

            // Decide which items to show
            var displayItems;
            if (showAll) {
                displayItems = items;
            } else {
                displayItems = items.slice(0, 6);
            }

            // Loop through each task
            for (var i = 0; i < displayItems.length; i++) {
                var task = displayItems[i];
                var li = createTaskElement(task);
                list.appendChild(li);
            }

            // Show or hide "Show All" button
            var showAllBtn = document.querySelector('#show-all-btn');
            if (items.length > 6 && !showAll) {
                showAllBtn.style.display = 'block';
            } else {
                showAllBtn.style.display = 'none';
            }
        });
}

// Add a new task
function addTask(title) {
    fetch('list.php?action=add', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title: title })
    })
    .then(function(res) {
        if (!res.ok) {
            alert('Failed to add task');
            return null;
        }
        return res.json();
    })
    .then(function(task) {
        if (task) {
            var li = createTaskElement(task);
            list.prepend(li);
        }
    });
}

//  Delete a task 
function deleteTask(id) {
    fetch('list.php?action=delete&id=' + encodeURIComponent(id))
        .then(function(res) {
            if (!res.ok) {
                alert('Failed to delete');
                return;
            }
            var li = list.querySelector('li[data-id="' + id + '"]');
            if (li) {
                li.remove();
            }
        });
}

//  Toggle task done/undone 
function toggleTask(id) {
    fetch('list.php?action=toggle&id=' + encodeURIComponent(id))
        .then(function(res) {
            if (!res.ok) {
                alert('Failed to update');
                return;
            }
            var li = list.querySelector('li[data-id="' + id + '"]');
            if (li) {
                li.classList.toggle('checked');
            }
        });
}

//  Update task title 
function updateTask(id, title) {
    fetch('list.php?action=update&id=' + encodeURIComponent(id), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title: title })
    })
    .then(function(res) {
        if (!res.ok) {
            alert('Failed to update');
        }
    });
}

//  Click "Add" button 
addBtn.addEventListener('click', function() {
    var title = input.value.trim();

    if (!title) {
        alert('Please type something first');
        return;
    }

    addTask(title);
    input.value = '';
    input.focus();
});

//  Press Enter to add 
input.addEventListener('keydown', function(ev) {
    if (ev.key === 'Enter') {
        addBtn.click();
    }
});

//  When page loads 
document.addEventListener('DOMContentLoaded', function() {
    loadTasks(false);
});