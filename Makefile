PID_FILE := .npm_dev.pid

setup:
	sh setup.sh

start: stop
	@echo "Using .env.example for local development..."
	cp backend/.env.example backend/.env

	@echo "Starting backend containers (docker-compose up -d)..."
	docker-compose up -d
	@echo "Killing any process on port 5173..."
	@lsof -t -i:5173 | xargs -r kill -9 || true
	@echo "Starting frontend (npm run dev on port 5173)..."
	cd frontend && npm run dev -- --port 5173 & echo $$! > $(PID_FILE)
	@echo "npm run dev started with PID $$(cat $(PID_FILE))"

stop:
	@echo "Stopping backend containers (docker-compose down)..."
	docker-compose down
	@if [ -f $(PID_FILE) ]; then \
		echo "Stopping frontend (vite)..."; \
		kill $$(cat $(PID_FILE)) 2>/dev/null || true; \
		rm -f $(PID_FILE); \
	fi
	@echo "Stop completed."

# ngrok公開用
preview:
	@echo "Using host-mode .env for preview..."
	cp backend/.env.preview backend/.env

	@echo "Ensuring MySQL docker is up..."
	docker-compose up -d mysql

	@echo "Clearing Laravel caches (host)..."
	cd backend && php artisan optimize:clear || true

	@echo "Building frontend for production..."
	cd frontend && npm run build

	@echo "Cleaning Laravel public directory (keeping index.php, .htaccess, storage)..."
	find backend/public -mindepth 1 -maxdepth 1 ! -name '.htaccess' ! -name 'index.php' ! -name 'storage' -exec rm -rf {} +

	@echo "Copying built files to Laravel public..."
	cp -r frontend/dist/* backend/public/

	@echo "Killing any process on port 8000..."
	@lsof -t -i:8000 | xargs -r kill -9 || true

	@echo "🖥️ Starting Laravel server on port 8000 (host)..."
	cd backend && php artisan serve --host=0.0.0.0 --port=8000 & \
	echo $$! > ../.laravel_preview.pid
	sleep 2

	@echo "Launching ngrok (inside backend dir)..."
	cd backend && ngrok http 8000

	@echo "Cleaning up Laravel server process..."
	@kill $$(cat .laravel_preview.pid) 2>/dev/null || true
	@rm -f .laravel_preview.pid

