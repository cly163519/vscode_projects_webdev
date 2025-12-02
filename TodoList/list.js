// Get HTML elements 
var list = document.querySelector('#task-list');
var input = document.querySelector('#task-input');
var addBtn = document.querySelector('#add-btn');

// Create a task <li> element
function createTaskElement(task) {
    // task is an object: { id: 1, title: "Buy milk", done: 0 }

    var li = document.createElement('li');   // Create new <li> tag
    li.dataset.id = task.id;    /*li你刚才创建的变量名, .dataset系统自带用来设置 data-xxx 属性, 
                                .id (左边)你自己写的可以叫 .abc，会变成 data-abc, task函数参数传进来的数据, 
                                .id (右边)数据里的字段服务器返回的数据里叫 id*/

    li.textContent = task.title; // Set text inside <li>

    if (task.done == 1) {
        li.classList.add('checked');
        // classList.add() → Add a CSS class to element
        // Result: <li class="checked">
    }

    // Create delete button
    var removeBtn = document.createElement('button');
    removeBtn.className = 'close';           // Same as classList.add('close')
    removeBtn.textContent = '×';             // Button shows × symbol
    li.appendChild(removeBtn);
    // appendChild() → Put removeBtn inside li
    // Result: <li>Buy milk<button class="close">×</button></li>

    // Click li to toggle
    li.addEventListener('click', function(ev) {
        // addEventListener('click', ...) → Run this function when clicked
        // ev = event object, contains info about what happened

        if (ev.target === removeBtn) {
            // ev.target → The exact element user clicked
            // If user clicked the × button, don't toggle, just return
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
        // 'dblclick' → Double click event (click twice fast)

        var oldTitle = task.title;

        var editInput = document.createElement('input');
        editInput.type = 'text';
        editInput.value = oldTitle;
        editInput.style.width = '90%';
        editInput.style.fontSize = '20px';
        editInput.style.border = 'none';
        editInput.style.outline = 'none';

        li.textContent = '';                 // Clear all text in <li>
        li.appendChild(editInput);           // Put input box in <li>
        editInput.focus();
        // focus() → Auto click into the input box, ready to type

        function save() {
            var newTitle = editInput.value.trim();
            // .trim() → Remove spaces from start and end
            // "  hello  ".trim() → "hello"

            if (newTitle && newTitle !== oldTitle) {
                // newTitle → true if not empty string
                // !== → "not equal to"
                task.title = newTitle;
                li.textContent = newTitle;
                li.appendChild(removeBtn);   // Add back the × button
                updateTask(task.id, newTitle);
            } else {
                li.textContent = oldTitle;   // Restore old title
                li.appendChild(removeBtn);
            }
        }

        editInput.addEventListener('blur', save);
        // 'blur' → When input loses focus (click outside)

        editInput.addEventListener('keydown', function(ev) {
            // 'keydown' → When any key is pressed
            if (ev.key === 'Enter') {
                editInput.blur();
                // blur() → Make input lose focus → triggers 'blur' event → calls save()
            }
        });
    });

    return li;
}

// Press Enter to add   
function loadTasks(showAll) {
    fetch('list.php?action=list')
    // fetch() → Send HTTP request to server
    // Same as typing "list.php?action=list" in browser address bar

        .then(function(res) {
            // .then() → "When fetch finishes, do this"
            // res = response from server

            return res.json();
            // res.json() → Convert response text to JavaScript object
            // '{"id":1}' → {id: 1}
        })

        .then(function(items) {
            // Second .then() → "When res.json() finishes, do this"
            // items = array of tasks: [{id:1, title:"...", done:0}, ...]

            list.innerHTML = '';
            // innerHTML = '' → Delete all children inside <ul>

            var displayItems;
            if (showAll) {
                displayItems = items;
            } else {
                displayItems = items.slice(0, 6);
                // slice(0, 6) → Get first 6 items
                // [a,b,c,d,e,f,g,h].slice(0,6) → [a,b,c,d,e,f]
            }

            for (var i = 0; i < displayItems.length; i++) {
                var task = displayItems[i];
                var li = createTaskElement(task);
                list.appendChild(li);
            }

            var showAllBtn = document.querySelector('#show-all-btn');
            if (items.length > 6 && !showAll) {
                // !showAll → "not showAll" → true if showAll is false
                showAllBtn.style.display = 'block';  // Show button
            } else {
                showAllBtn.style.display = 'none';   // Hide button
            }
        });
}

// Press Enter to add   
function addTask(title) {
    fetch('list.php?action=add', {
        method: 'POST',
        // POST → Send data to server (hidden in request body)
        // GET → Send data in URL (visible: ?title=xxx)

        headers: { 'Content-Type': 'application/json' },
        // Tell server: "I'm sending JSON format data"

        body: JSON.stringify({ title: title })
        // JSON.stringify() → Convert JS object to JSON string
        // { title: "Buy milk" } → '{"title":"Buy milk"}'
    })
    .then(function(res) {
        if (!res.ok) {
            // res.ok → true if status is 200-299 (success)
            // !res.ok → Request failed
            alert('Failed to add task');
            return null;
        }
        return res.json();
    })
    .then(function(task) {
        if (task) {
            var li = createTaskElement(task);
            list.prepend(li);
            // prepend() → Add to the BEGINNING of list
            // appendChild() → Add to the END of list
        }
    });
}

// Press Enter to add   
function deleteTask(id) {
    fetch('list.php?action=delete&id=' + encodeURIComponent(id))
    // encodeURIComponent() → Make special characters safe for URL
    // "hello world" → "hello%20world"
    // Usually not needed for numbers, but good practice

        .then(function(res) {
            if (!res.ok) {
                alert('Failed to delete');
                return;
            }

            var li = list.querySelector('li[data-id="' + id + '"]');
            // querySelector('li[data-id="5"]') → Find <li data-id="5">

            if (li) {
                li.remove();
                // remove() → Delete this element from page
            }
        });
}

// Press Enter to add   
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
                // classList.toggle() → If has class, remove it; if not, add it
                // Like a light switch: on → off → on → off
            }
        });
}

// Press Enter to add   
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

// Click "Add" button 
addBtn.addEventListener('click', function() {
    var title = input.value.trim();
    // input.value → Get text user typed in input box

    if (!title) {
        // !title → true if title is empty string ""
        // Empty string is "falsy" in JavaScript
        alert('Please type something first');
        return;
        // return → Stop here, don't run code below
    }

    addTask(title);
    input.value = '';                        // Clear the input box
    input.focus();                           // Focus back to input box
});

// Press Enter to add   
input.addEventListener('keydown', function(ev) {
    if (ev.key === 'Enter') {
        // ev.key → Which key was pressed
        // 'Enter', 'Escape', 'a', 'b', etc.

        addBtn.click();
        // click() → Simulate clicking the button
    }
});

// When page loads
document.addEventListener('DOMContentLoaded', function() {
    // 'DOMContentLoaded' → When HTML is fully loaded
    // This makes sure all elements exist before we use them

    loadTasks(false);
    // false → Don't show all, only show first 6
});