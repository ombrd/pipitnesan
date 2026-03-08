.PHONY: setup up down share restart shell

# Target untuk menginisialisasi project (copy env, install composer di container)
setup:
	@echo "Copying .env.example to .env (if not exists)..."
	@cp -n .env.example .env || true
	@echo "Installing Composer dependencies via temporary container..."
	docker run --rm \
		-u "$$(id -u):$$(id -g)" \
		-v "$$(pwd):/var/www/html" \
		-w /var/www/html \
		laravelsail/php84-composer:latest \
		composer install --ignore-platform-reqs
	@echo "Setup complete! Now run 'make up' to start the application."

# Target untuk menyalakan Laravel Sail (Docker)
up:
	@echo "Starting the application..."
	./vendor/bin/sail up -d
	@echo "Application is running at http://localhost"

# Target untuk mematikan Laravel Sail
down:
	@echo "Stopping the application..."
	./vendor/bin/sail down

# Target untuk merestart Laravel Sail
restart:
	@echo "Restarting the application..."
	./vendor/bin/sail down
	./vendor/bin/sail up -d

# Target untuk masuk ke dalam terminal container aplikasi
shell:
	./vendor/bin/sail shell

# Target untuk mempublish aplikasi ke public menggunakan Ngrok
share:
	@echo "Sharing application via Ngrok tunnel..."
	./vendor/bin/sail share
