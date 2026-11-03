# Portfolio

## Docker development setup

The Compose file builds PHP and Node runtime images independently of the
application source. The source is mounted at runtime from:

- `backend_laravel-app` for the Laravel application
- `frontend_vuejs-app` for the Vue application

Place each application in its corresponding folder, then start the services:

```powershell
docker compose up --build
```

The backend is available at <http://localhost:8001>, the frontend at
<http://localhost:5173>, and phpMyAdmin at <http://localhost:9000>.

If either application folder is missing or does not contain its project
manifest (`composer.json` or `package.json`), its development container stays
idle; Docker cannot run the application until its source is present.

//command for create many model in one command//
for name in Profile Project ProjectImage Skill ProjectSkill Experience Education SocialLink Message; do php artisan make:model "$name"; done
//
