# TODO: Przepisanie struktury na projekty zawierające zadania

- [x] Zaktualizować schema.sql: dodać tabelę projects, dodać project_id do tasks
- [x] Dodać src/model/Project.php
- [x] Dodać src/repository/ProjectRepository.php
- [x] Zaktualizować src/model/Task.php: dodać project_id
- [x] Zaktualizować src/repository/TaskRepository.php: metody dla project_id
- [x] Zaktualizować src/controllers/DashboardController.php: pobierać projekty i zadania per projekt
- [x] Zaktualizować public/views/dashboard.html: wyświetlać projekty z zadaniami
- [x] Zaktualizować Routing.php: dodać trasy dla projektów
- [x] Zaktualizować TODO.md: oznaczyć jako ukończone
