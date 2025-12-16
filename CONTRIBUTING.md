# Contributing to CoreSkool

Thank you for your interest in contributing to CoreSkool! This document provides guidelines for contributing to the project.

## Code of Conduct

We are committed to providing a welcoming and inspiring community for all. Please be respectful and constructive in your interactions.

## How to Contribute

### Reporting Bugs

If you find a bug, please create an issue with:
- Clear description of the bug
- Steps to reproduce
- Expected behavior
- Actual behavior
- Screenshots (if applicable)
- Environment details (PHP version, MySQL version, browser)

### Suggesting Features

For feature requests:
- Check if the feature is already planned in FEATURES.md
- Provide a clear use case
- Explain why this feature would be useful
- Consider implementation details

### Code Contributions

1. **Fork the Repository**
   ```bash
   git clone https://github.com/Corestreamng/coreskool.git
   cd coreskool
   ```

2. **Create a Branch**
   ```bash
   git checkout -b feature/your-feature-name
   # or
   git checkout -b fix/bug-description
   ```

3. **Make Your Changes**
   - Follow the coding standards below
   - Test your changes thoroughly
   - Update documentation if needed

4. **Commit Your Changes**
   ```bash
   git add .
   git commit -m "Description of your changes"
   ```

5. **Push to Your Fork**
   ```bash
   git push origin feature/your-feature-name
   ```

6. **Create a Pull Request**
   - Provide a clear description of changes
   - Reference any related issues
   - Ensure all tests pass

## Coding Standards

### PHP Code Style

- **PSR-12**: Follow PSR-12 coding standard
- **Naming**: Use camelCase for variables, PascalCase for classes
- **Comments**: Add comments for complex logic
- **Functions**: Keep functions small and focused
- **Error Handling**: Use try-catch blocks appropriately

Example:
```php
<?php
/**
 * Calculate student average score
 * 
 * @param array $scores Array of scores
 * @return float Average score
 */
function calculateAverage($scores) {
    if (empty($scores)) {
        return 0;
    }
    return array_sum($scores) / count($scores);
}
```

### JavaScript Code Style

- Use `const` and `let`, avoid `var`
- Use meaningful variable names
- Add comments for complex logic
- Use arrow functions where appropriate
- Follow ES6+ standards

Example:
```javascript
/**
 * Validate email address
 * @param {string} email - Email address to validate
 * @returns {boolean} True if valid
 */
function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}
```

### CSS Code Style

- Use meaningful class names
- Follow BEM naming convention where appropriate
- Use CSS variables for colors
- Keep selectors specific but not overly complex
- Add comments for complex styles

### Database

- Use prepared statements always
- Follow naming convention (lowercase with underscores)
- Add indexes for frequently queried columns
- Use foreign keys for relationships
- Document complex queries

## Testing

Before submitting:
- Test all new features
- Test on different browsers (Chrome, Firefox, Safari, Edge)
- Test on mobile devices
- Check for responsive design issues
- Verify no console errors
- Test with different user roles

## Documentation

Update documentation when:
- Adding new features
- Changing existing functionality
- Modifying configuration
- Adding new dependencies

Documentation to update:
- README.md (if general information changes)
- FEATURES.md (for new features)
- DEPLOYMENT.md (for deployment changes)
- Inline code comments
- API documentation

## Pull Request Guidelines

### Good PR Description

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Tested on Chrome
- [ ] Tested on Firefox
- [ ] Tested on Mobile
- [ ] All user roles tested

## Screenshots
(if applicable)

## Related Issues
Fixes #123
```

### PR Checklist

- [ ] Code follows project style guidelines
- [ ] Self-reviewed the code
- [ ] Commented complex code
- [ ] Updated documentation
- [ ] No new warnings generated
- [ ] Tested thoroughly
- [ ] No breaking changes (or documented)

## Areas for Contribution

We especially welcome contributions in:

### High Priority
- Testing and bug fixes
- Security improvements
- Performance optimization
- Documentation improvements
- Accessibility enhancements

### Features Needed
- Teacher management module completion
- Class management module
- Attendance system
- Examination system
- CBT system
- LMS system
- Mobile responsiveness improvements

### Nice to Have
- Additional language translations
- UI/UX improvements
- Additional themes
- Integration with third-party services

## Development Setup

1. **Requirements**
   - PHP 7.4+
   - MySQL 5.7+
   - Apache/Nginx
   - Git

2. **Local Setup**
   ```bash
   # Clone repository
   git clone https://github.com/Corestreamng/coreskool.git
   cd coreskool
   
   # Configure database
   cp config/database.php.example config/database.php
   # Edit with your credentials
   
   # Run installer
   php -S localhost:8000
   # Visit http://localhost:8000/install.php
   ```

3. **Development Tools**
   - VS Code (recommended)
   - PHP Intelephense extension
   - MySQL Workbench
   - Postman (for API testing)
   - Browser DevTools

## Git Workflow

1. Keep your fork updated
   ```bash
   git remote add upstream https://github.com/Corestreamng/coreskool.git
   git fetch upstream
   git merge upstream/main
   ```

2. Create feature branches from main
3. Commit frequently with clear messages
4. Push to your fork
5. Create pull request to main branch

## Commit Message Guidelines

Format:
```
<type>: <subject>

<body>

<footer>
```

Types:
- **feat**: New feature
- **fix**: Bug fix
- **docs**: Documentation
- **style**: Formatting
- **refactor**: Code restructuring
- **test**: Adding tests
- **chore**: Maintenance

Example:
```
feat: Add student bulk import

- Added CSV import functionality
- Validates data before import
- Shows progress during import
- Handles duplicate entries

Closes #45
```

## Code Review Process

1. All contributions require code review
2. Reviewers may request changes
3. Address feedback promptly
4. Once approved, maintainer will merge

## Getting Help

- **Documentation**: Check README.md and other docs
- **Issues**: Search existing issues
- **Discussions**: Use GitHub Discussions for questions
- **Email**: admin@coreskool.coinswipe.xyz

## License

By contributing, you agree that your contributions will be licensed under the same license as the project.

## Recognition

Contributors will be:
- Listed in CONTRIBUTORS.md
- Mentioned in release notes (for significant contributions)
- Credited in documentation

## Questions?

Feel free to reach out:
- Create an issue for technical questions
- Email for general inquiries
- Join our community discussions

Thank you for contributing to CoreSkool! 🎓

---

**Last Updated**: December 2024
