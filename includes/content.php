<?php

function get_portfolio_items(PDO $connection, string $section): array
{
    $tables = [
        'skills' => 'skills',
        'projects' => 'projects',
        'education' => 'education',
        'experience' => 'experience',
        'achievements' => 'achievements',
    ];

    if (!isset($tables[$section])) {
        throw new InvalidArgumentException('Unknown portfolio section.');
    }

    $table = $tables[$section];
    return $connection->query(
        'SELECT * FROM `' . $table . '` ORDER BY sort_order, id'
    )->fetchAll();
}

function get_portfolio_profile(PDO $connection): array
{
    $statement = $connection->prepare('SELECT * FROM profile WHERE id = :id LIMIT 1');
    $statement->execute(['id' => 1]);
    return $statement->fetch() ?: [];
}

function validate_skill_input(array $skill): array
{
    $name = $skill['name'] ?? null;
    $category = $skill['category'] ?? null;
    if (!is_string($name) || !is_string($category) || trim($name) === '' || trim($category) === '') {
        throw new InvalidArgumentException('Enter a skill name and category.');
    }

    $nameLength = preg_match_all('/./us', $name);
    $categoryLength = preg_match_all('/./us', $category);
    if ($nameLength === false || $nameLength > 100 || $categoryLength === false || $categoryLength > 100) {
        throw new InvalidArgumentException('Enter a skill name and category.');
    }

    $input = $skill['proficiency'] ?? '';
    if ($input !== '' && !is_string($input) && !is_int($input)) {
        throw new InvalidArgumentException('Skill proficiency must be a whole number.');
    }
    $proficiency = $input === '' ? null : filter_var($input, FILTER_VALIDATE_INT);
    if ($proficiency !== null && ($proficiency === false || $proficiency < 0 || $proficiency > 100)) {
        throw new InvalidArgumentException('Skill proficiency must be between 0 and 100.');
    }

    return ['name' => trim($name), 'category' => trim($category), 'proficiency' => $proficiency];
}

function create_skill(PDO $connection, array $skill): int
{
    $values = validate_skill_input($skill);
    $statement = $connection->prepare('INSERT INTO skills (name, category, proficiency) VALUES (:name, :category, :proficiency)');
    $statement->execute($values);
    return (int) $connection->lastInsertId();
}

function update_skill(PDO $connection, string $skillId, array $skill): void
{
    $id = filter_var($skillId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) {
        throw new InvalidArgumentException('Choose a valid skill.');
    }

    $values = validate_skill_input($skill);
    $values['id'] = $id;
    $statement = $connection->prepare(
        'UPDATE skills SET name = :name, category = :category,
         proficiency = :proficiency WHERE id = :id'
    );
    $statement->execute($values);
    if ($statement->rowCount() === 0 && !$connection->query('SELECT id FROM skills WHERE id = ' . (int) $id)->fetchColumn()) {
        throw new RuntimeException('The skill was not found.');
    }
}

function delete_skill(PDO $connection, string $skillId): bool
{
    $id = filter_var($skillId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) {
        throw new InvalidArgumentException('Choose a valid skill.');
    }

    $statement = $connection->prepare('DELETE FROM skills WHERE id = :id');
    $statement->execute(['id' => $id]);
    return $statement->rowCount() === 1;
}

function update_portfolio_profile(PDO $connection, array $profile): void
{
    $limits = [
        'full_name' => 150,
        'headline' => 200,
        'introduction' => 2000,
        'biography' => 5000,
        'email' => 254,
        'facebook_url' => 255,
        'instagram_url' => 255,
    ];
    $values = [];
    foreach ($limits as $field => $limit) {
        $value = $profile[$field] ?? null;
        $length = is_string($value) ? preg_match_all('/./us', trim($value)) : false;
        $optional = in_array($field, ['facebook_url', 'instagram_url'], true);
        if ($length === false || $length > $limit || (!$optional && trim($value) === '')) {
            throw new InvalidArgumentException('Check the profile fields and their lengths.');
        }
        $values[$field] = trim($value);
    }

    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Enter a valid email address.');
    }
    foreach (['facebook_url', 'instagram_url'] as $field) {
        $scheme = parse_url($values[$field], PHP_URL_SCHEME);
        if ($values[$field] !== '' && (!filter_var($values[$field], FILTER_VALIDATE_URL)
            || !is_string($scheme) || !in_array(strtolower($scheme), ['http', 'https'], true)
        )) {
            throw new InvalidArgumentException('Social links must use http or https.');
        }
    }

    $image = $profile['profile_image'] ?? null;
    if (!is_string($image) || !preg_match('/^(images|uploads)\/[a-zA-Z0-9._-]+\.(jpe?g|png|gif|webp)$/i', $image)) {
        throw new InvalidArgumentException('Choose a valid local profile image.');
    }
    $values['profile_image'] = $image;

    $statement = $connection->prepare(
        'UPDATE profile SET full_name = :full_name, headline = :headline,
         introduction = :introduction, biography = :biography, email = :email,
         facebook_url = :facebook_url, instagram_url = :instagram_url,
         profile_image = :profile_image WHERE id = 1'
    );
    $statement->execute($values);
    if ($statement->rowCount() === 0 && !$connection->query('SELECT id FROM profile WHERE id = 1')->fetchColumn()) {
        throw new RuntimeException('The portfolio profile was not found.');
    }
}

function get_portfolio_settings(PDO $connection): array
{
    $statement = $connection->query(
        "SELECT setting_key, setting_value FROM website_settings
         WHERE setting_key IN ('site_title', 'footer_text')"
    );
    $settings = $statement->fetchAll(PDO::FETCH_KEY_PAIR);
    if (!isset($settings['site_title'], $settings['footer_text'])) {
        throw new RuntimeException('Website settings have not been initialized.');
    }

    return $settings;
}

function update_portfolio_settings(PDO $connection, array $settings): void
{
    $values = [];
    foreach (['site_title' => 150, 'footer_text' => 300] as $key => $limit) {
        $value = $settings[$key] ?? null;
        $length = is_string($value) ? preg_match_all('/./us', trim($value)) : false;
        if ($length === false || $length === 0 || $length > $limit) {
            throw new InvalidArgumentException('Check the website title and footer text.');
        }
        $values[$key] = trim($value);
    }

    $statement = $connection->prepare(
        'INSERT INTO website_settings (setting_key, setting_value)
         VALUES (:title_key, :site_title), (:footer_key, :footer_text)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $statement->execute([
        'title_key' => 'site_title',
        'site_title' => $values['site_title'],
        'footer_key' => 'footer_text',
        'footer_text' => $values['footer_text'],
    ]);
}

function get_managed_content_config(string $section): array
{
    $text = static function (string $label, int $maxLength, bool $required = true): array {
        return ['label' => $label, 'type' => 'text', 'max' => $maxLength, 'required' => $required];
    };
    $area = static function (string $label, int $maxLength, bool $required = true): array {
        return ['label' => $label, 'type' => 'textarea', 'max' => $maxLength, 'required' => $required];
    };
    $order = ['label' => 'Display order', 'type' => 'number', 'min' => 0, 'max' => 9999, 'default' => 0];
    $config = [
        'projects' => [
            'title' => 'Projects',
            'fields' => [
                'title' => $text('Title', 180),
                'description' => $area('Description', 5000),
                'category' => ['label' => 'Category', 'type' => 'select', 'options' => ['Web', 'Design', 'Database']],
                'image_path' => ['label' => 'Project image', 'type' => 'image', 'required' => false],
                'technologies' => $text('Technologies (comma separated)', 500, false),
                'sort_order' => $order,
            ],
        ],
        'education' => [
            'title' => 'Education',
            'fields' => [
                'school' => $text('School', 180),
                'program' => $text('Program', 180),
                'start_year' => ['label' => 'Start year', 'type' => 'number', 'min' => 1900, 'max' => 2200, 'nullable' => true],
                'end_year' => ['label' => 'End year', 'type' => 'number', 'min' => 1900, 'max' => 2200, 'nullable' => true],
                'description' => $area('Description', 5000, false),
                'sort_order' => $order,
            ],
        ],
        'experience' => [
            'title' => 'Experience',
            'fields' => [
                'title' => $text('Title', 180),
                'organization' => $text('Organization', 180, false),
                'date_label' => $text('Date or period', 100, false),
                'description' => $area('Description', 5000),
                'sort_order' => $order,
            ],
        ],
        'achievements' => [
            'title' => 'Achievements',
            'fields' => [
                'title' => $text('Title', 180),
                'description' => $area('Description', 5000),
                'date_label' => $text('Date or period', 100, false),
                'sort_order' => $order,
            ],
        ],
    ];

    if (!isset($config[$section])) {
        throw new InvalidArgumentException('Choose a valid content section.');
    }

    return $config[$section];
}

function get_managed_content_record(PDO $connection, string $section, string $recordId): array
{
    $config = get_managed_content_config($section);
    $id = filter_var($recordId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) {
        throw new InvalidArgumentException('Choose a valid content item.');
    }

    $statement = $connection->prepare('SELECT * FROM `' . $section . '` WHERE id = :id');
    $statement->execute(['id' => $id]);
    return $statement->fetch() ?: [];
}

function save_managed_content(PDO $connection, string $section, array $input, ?string $recordId = null): int
{
    $config = get_managed_content_config($section);
    $values = [];
    foreach ($config['fields'] as $field => $rules) {
        $value = $input[$field] ?? '';
        if ($rules['type'] === 'number') {
            if ($value === '' && isset($rules['nullable'])) {
                $values[$field] = null;
                continue;
            }
            if ($value === '' && isset($rules['default'])) {
                $values[$field] = $rules['default'];
                continue;
            }
            $number = is_string($value) || is_int($value) ? filter_var($value, FILTER_VALIDATE_INT) : false;
            if ($number === false || $number < $rules['min'] || $number > $rules['max']) {
                throw new InvalidArgumentException('Check the numeric content fields.');
            }
            $values[$field] = $number;
            continue;
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException('Check the content fields.');
        }
        $value = trim($value);
        $length = preg_match_all('/./us', $value);
        if ($length === false || (isset($rules['max']) && $length > $rules['max'])
            || (!empty($rules['required']) && $value === '')
        ) {
            throw new InvalidArgumentException('Check the content fields and their lengths.');
        }
        if ($rules['type'] === 'select' && !in_array($value, $rules['options'], true)) {
            throw new InvalidArgumentException('Choose a valid content category.');
        }
        if ($rules['type'] === 'image' && $value !== ''
            && !preg_match('/^(images|uploads)\/[a-zA-Z0-9._-]+\.(jpe?g|png|gif|webp)$/i', $value)
        ) {
            throw new InvalidArgumentException('Choose a valid local project image.');
        }
        $values[$field] = $value;
    }

    if ($section === 'education' && $values['start_year'] !== null && $values['end_year'] !== null
        && $values['start_year'] > $values['end_year']
    ) {
        throw new InvalidArgumentException('The end year must not be before the start year.');
    }

    if ($recordId === null) {
        $fields = array_keys($values);
        $columns = '`' . implode('`, `', $fields) . '`';
        $placeholders = ':' . implode(', :', $fields);
        $statement = $connection->prepare(
            'INSERT INTO `' . $section . '` (' . $columns . ') VALUES (' . $placeholders . ')'
        );
        $statement->execute($values);
        return (int) $connection->lastInsertId();
    }

    $id = filter_var($recordId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) {
        throw new InvalidArgumentException('Choose a valid content item.');
    }
    $updates = [];
    foreach (array_keys($values) as $field) {
        $updates[] = '`' . $field . '` = :' . $field;
    }
    $values['id'] = $id;
    $statement = $connection->prepare(
        'UPDATE `' . $section . '` SET ' . implode(', ', $updates) . ' WHERE id = :id'
    );
    $statement->execute($values);
    if ($statement->rowCount() === 0 && !$connection->query(
        'SELECT id FROM `' . $section . '` WHERE id = ' . (int) $id
    )->fetchColumn()) {
        throw new RuntimeException('The content item was not found.');
    }

    return (int) $id;
}

function delete_managed_content(PDO $connection, string $section, string $recordId): bool
{
    get_managed_content_config($section);
    $id = filter_var($recordId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) {
        throw new InvalidArgumentException('Choose a valid content item.');
    }

    $statement = $connection->prepare('DELETE FROM `' . $section . '` WHERE id = :id');
    $statement->execute(['id' => $id]);
    return $statement->rowCount() === 1;
}
