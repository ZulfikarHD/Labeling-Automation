# Feature Test Documentation

This directory contains documentation for feature tests and templates for creating consistent test documentation.

## Available Templates

### 1. Comprehensive Template
**File**: `feature_test_documentation_template.md`

Use this template for complex feature tests that involve:
- Multiple controllers or services
- Complex business logic
- Multiple test categories
- Extensive helper methods
- Performance considerations
- Troubleshooting needs

**Best for**: Major feature tests like authentication, order processing, complex workflows.

### 2. Simple Template
**File**: `simple_feature_test_template.md`

Use this template for straightforward feature tests that involve:
- Single controller testing
- Basic CRUD operations
- Limited test methods
- Simple validation testing

**Best for**: Basic controller tests, simple API endpoint tests, utility tests.

## Documentation Standards

### File Naming Convention
```
[snake_case_test_name]_feature_test.md
```

Examples:
- `print_label_inspeksi_feature_test.md`
- `user_authentication_feature_test.md`
- `order_management_feature_test.md`

### Test File Reference
Always add a reference to the documentation in your test file:

```php
/**
 * Feature test untuk [YourController]
 * 
 * @see Docs/Tests/FeatureTests/[your_test_documentation].md untuk dokumentasi lengkap
 */
class YourFeatureTest extends TestCase
{
    // ... test code
}
```

## Template Usage Guidelines

### 1. Choose the Right Template
- **Comprehensive**: Use for tests with 10+ methods or complex scenarios
- **Simple**: Use for tests with fewer than 10 methods and straightforward logic

### 2. Replace Placeholders
- All content in `[brackets]` should be replaced with actual information
- Remove sections that don't apply to your specific test
- Add additional sections if needed for your use case

### 3. Keep Documentation Updated
- Update documentation when tests change
- Add new test methods to the documentation
- Update results and statistics after significant changes

### 4. Use Indonesian for Business Logic
- Use Indonesian for business logic descriptions (as per project standards)
- Keep technical terms in English
- Use clear, concise language

## Documentation Sections Explained

### Essential Sections (Always Include)
1. **Overview**: Brief description of what the test covers
2. **Test Information**: Basic metadata about the test
3. **Tested Features**: List of main features being tested
4. **Test Methods**: Documentation of each test method
5. **Results**: Summary of test outcomes

### Optional Sections (Include When Relevant)
1. **Helper Methods**: For tests with complex setup
2. **Performance Considerations**: For tests with timing concerns
3. **Troubleshooting**: For tests that commonly fail
4. **Dependencies**: For tests with external requirements
5. **Future Improvements**: For tests that need enhancement

## Quality Standards

### Documentation Should Be:
- **Clear**: Easy to understand for new team members
- **Complete**: Covers all important aspects of the test
- **Current**: Updated with the latest test changes
- **Consistent**: Follows the established template format
- **Practical**: Includes useful information for maintenance

### Documentation Should Include:
- Purpose of each test method
- What setup is required
- What assertions are made
- Any special considerations or edge cases
- Links to related documentation

## Examples

### Good Documentation Examples
- `print_label_inspeksi_feature_test.md` - Comprehensive example
- Shows proper use of template
- Complete documentation of all aspects
- Clear explanations and organization

### Documentation Checklist
Before considering documentation complete, verify:

- [ ] All placeholders have been replaced
- [ ] All test methods are documented
- [ ] Helper methods are explained
- [ ] Test results are current
- [ ] Related files are listed
- [ ] Routes are documented
- [ ] Business logic is explained in Indonesian
- [ ] Technical details are in English
- [ ] Examples are provided where helpful
- [ ] Troubleshooting info is included (if needed)

## Maintenance

### Regular Updates
- Review documentation monthly
- Update after major test changes
- Verify results after test improvements
- Add new insights as you learn more about the tests

### Version Control
- Document significant changes in the Changelog section
- Use meaningful commit messages when updating documentation
- Consider tagging major documentation updates

## Integration with Development Workflow

### When to Create Documentation
1. **New Feature Tests**: Create documentation immediately after writing the test
2. **Existing Tests**: Add documentation when refactoring or enhancing tests
3. **Complex Tests**: Always document tests that are hard to understand

### Code Review Process
- Include documentation review as part of test code reviews
- Ensure documentation matches the actual test implementation
- Verify that documentation follows the template standards

### Onboarding New Team Members
- Use these documented tests as examples for new developers
- Include documentation review in test writing training
- Reference documentation during code walkthroughs

## Related Documentation

- [Controller Documentation Template](../Controller/Controller_Documentation_Template.md)
- [Service Documentation](../Services/)
- [Feature Summary Template](../Feature_Summary_Template.md)

## Questions or Improvements

If you have suggestions for improving these templates or need clarification on documentation standards, please:

1. Review existing examples
2. Check with the team lead
3. Propose improvements through the standard review process

Remember: Good documentation makes tests more maintainable and helps the entire team understand the codebase better. 
