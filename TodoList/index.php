<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>To Do List</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="list.css" />
</head>
<body>
    <div class="todo-app">
      <header class="todo-header">
          <h1>My To Do List</h1>
          <div class="todo-input-row">
              <input type="text" id="task-input" placeholder="To do something...">
              <button id="add-btn">Add task</button>
          </div>
      </header>
    
      <ul id="task-list" class="todo-list">
        <!-- <li>Go to the library</li>
        <li>Read a book</li>
        <li>Talk with family</li>
        <li>Go to the restaurant</li>
        <li>Buy groceries</li>
        <li>Finish homework</li> -->
      </ul>
    </div>

  <script src="list.js"></script>
</body>
</html>