PID_FILE := .npm_dev.pid

start: stop
	@echo "Starting backend containers (docker-compose up -d)..."
	docker-compose up -d
	@echo "Starting frontend (npm run dev)..."
	cd frontend && npm run dev & echo $$! > $(PID_FILE)
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
