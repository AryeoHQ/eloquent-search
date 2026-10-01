## Overview

**snap** is a lightweight, project-local CLI wrapper for [Docker Compose](https://docs.docker.com/compose/). Once installed in your project root, you can run `snap <command>` instead of typing long
`docker compose …` invocations.

Key features:

* Laravel-specific shortcuts: `composer`, `phpstan`, `pint`, `test`
* Generic container helpers: `shell`, `exec`, `stats`
* Stack lifecycle: `up`, `down`, `build` (with AWS SSO & retry), `update`, `reload`, `cleanup`

---

## 📦 Installation

This approach doesn’t require Composer—just drop the script in your repo and add a small shim to your shell config.

1. **Define a shell shim**
   In your **\~/.bashrc** or **\~/.zshrc**, add the following. It ensures that when you run `snap`, it invokes the
   project’s local `./snap` if present:

   ```bash
   snap() {
     # Find project root (falls back to cwd if not in a Git repo)
     local root
     root="$(git rev-parse --show-toplevel 2>/dev/null || echo "$PWD")"
     if [ -x "$root/snap" ]; then
       # Run the local snap script
       "$root/snap" "$@"
     else
       # Fallback to any globally installed snap
       command snap "$@"
     fi
   }
   ```

2. **Reload your shell**

   ```bash
   source ~/.bashrc   # or ~/.zshrc
   ```

3. **Verify**
   From your project directory:

   ```bash
   which snap
   # Should point to your local ./snap via the shim

   snap --help
   # Displays usage summary
   ```

---

## ⚙️ Configuration

* **`SERVICE`**
  The Docker Compose service name (default: `php`).

---

## 🚀 Usage

```text
snap <command> [options…]
```

### Laravel-Specific

| Command    | What it does                                                         | Example                                   |
|------------|----------------------------------------------------------------------|-------------------------------------------|
| `composer` | Run Composer                                                         | `snap composer require guzzlehttp/guzzle` |
| `phpstan`  | Run PHPStan with package or tests config (`--tests` switches config) | `snap phpstan --tests src/`               |
| `pint`     | Run Laravel Pint (code style)                                        | `snap pint app/Models`                    |
| `test`     | Run Paratest (parallel PHPUnit)                                      | `snap test --max-processes=4`             |

### Generic Helpers

| Command   | What it does                                                                | Example                            |
|-----------|-----------------------------------------------------------------------------|------------------------------------|
| `shell`   | Open an interactive `sh` shell in your PHP container                        | `snap shell`                       |
| `exec`    | Run arbitrary command in any service: `snap exec <service> <cmd> [args…]`   | `snap exec mysql mysql -u root -p` |
| `stats`   | Show a one-time stats snapshot for your PHP container                       | `snap stats`                       |
| `build`   | build your PHP service                                                      | `snap build`                       |
| `update`  | Pull latest images and recreate your PHP container                          | `snap update`                      |
| `reload`  | Restart only the PHP service                                                | `snap reload`                      |
| `cleanup` | Prune unused Docker objects (`docker system prune -f`)                      | `snap cleanup`                     |
| `up`      | Bring up the entire Compose stack in detached mode (`docker compose up -d`) | `snap up`                          |
| `down`    | Tear down the entire Compose stack (`docker compose down`)                  | `snap down`                        |

---

## 🔄 Examples

```bash
# Start everything in the background
snap up

# Build (handles AWS SSO & retries)
snap build

# Run PHPStan on your tests directory
snap phpstan --tests tests/

# Open a DB shell (using exec)
snap exec mysql mysql -u root -p my_database

# Prune unused images/containers
snap cleanup

# Stop and remove all containers
snap down
```

---

## 🙋‍♀️ Troubleshooting

* **Shim not picking up local `snap`?**

    * Verify you reloaded your shell (`source ~/.bashrc` or `source ~/.zshrc`).
    * Ensure you’re in a Git repo or adjust the shim’s root-detection logic.
