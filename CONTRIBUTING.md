Thank you for considering contributing to phannp! This document explains how to get your changes accepted quickly.

Getting started
- Fork the repository and create a feature branch from `refactor-resources` (or `main` for non-work-in-progress changes).
- Run the test-suite and ensure all tests pass locally before opening a PR.

Run tests locally

```bash
composer install
./vendor/bin/phpunit --configuration phpunit.xml.dist
```

Coding standards
- Follow the existing project style. The repository includes a coding standard (phpcs) in `vendor/`.
- Keep changes focused and small. Each PR should implement a single behavior change or bugfix.

Commit messages
- Use clear, imperative commit messages. Example: `:sparkles: Add sms validation to SMS resource`.

Pull request checklist
- [ ] I have run the test-suite locally and all tests pass.
- [ ] I have added or updated tests for any new behavior.
- [ ] I have updated the README or documentation when applicable.
- [ ] My code follows the project's coding style.

How to open a PR
- Push your branch to your fork and open a pull request against `refactor-resources` (or `main` if the change is a release/fix).
- Describe the problem, how you've solved it, and list the files changed.
- Add any notes for reviewers (e.g., performance considerations, backward compatibility concerns).

Thanks again — we appreciate your time and contributions!
