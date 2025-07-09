<?php

$feature = ucfirst($argv[1] ?? '');

if (!$feature) {
    exit("❌ Please provide a feature name.\nUsage: php generate.php Timeline\n");
}

$camelCase = lcfirst($feature);

$basePath = __DIR__;
$stubsPath = $basePath . '/scripts/stubs';

$replacements = [
    '{{ClassName}}' => $feature,
    '{{classname}}' => $camelCase,
];

$map = [
    "{$basePath}/Domain/Entity/{$feature}.php" => "$stubsPath/entity.stub",
    "{$basePath}/Domain/Repository/{$feature}RepositoryInterface.php" => "$stubsPath/repository-interface.stub",
    "{$basePath}/Infrastructure/Persistence/Doctrine/{$feature}Repository.php" => "$stubsPath/repository.stub",
    "{$basePath}/Interface/Controller/{$feature}Controller.php" => "$stubsPath/controller.stub",
    "{$basePath}/Interface/Routes/{$camelCase}Routes.php" => "$stubsPath/routes.stub",
    "{$basePath}/Application/UseCase/{$feature}/Create{$feature}UseCase.php" => "$stubsPath/create-usecase.stub",
    "{$basePath}/Application/UseCase/{$feature}/Delete{$feature}UseCase.php" => "$stubsPath/delete-usecase.stub",
    "{$basePath}/Application/UseCase/{$feature}/Update{$feature}UseCase.php" => "$stubsPath/update-usecase.stub",
    "{$basePath}/Application/UseCase/{$feature}/Get{$feature}ByIdUseCase.php" => "$stubsPath/get-by-id-usecase.stub",
    "{$basePath}/Application/UseCase/{$feature}/GetAll{$feature}sUseCase.php" => "$stubsPath/get-all-usecase.stub",
];

foreach ($map as $target => $stubPath) {
    if (!file_exists($stubPath)) continue;

    $content = file_get_contents($stubPath);
    foreach ($replacements as $key => $value) {
        $content = str_replace($key, $value, $content);
    }

    $dir = dirname($target);
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    file_put_contents($target, $content);
    echo "✅ Created: $target\n";

    // Step 1: Print use-case imports
    echo "\n📌 Add the following to your controller setup:\n\n";

    echo "use Application\\UseCase\\{$feature}\\{\n";
    echo "    GetAll{$feature}sUseCase,\n";
    echo "    Get{$feature}ByIdUseCase,\n";
    echo "    Create{$feature}UseCase,\n";
    echo "    Update{$feature}UseCase,\n";
    echo "    Delete{$feature}UseCase\n";
    echo "};\n\n";

    // Step 2: Print repository initialization
    echo "\$${camelCase}Repo = new {$feature}Repository(\$em);\n\n";

    // Step 3: Print controller constructor
    echo "\$${camelCase}Controller = new {$feature}Controller(\n";
    echo "    new GetAll{$feature}sUseCase(\$${camelCase}Repo),\n";
    echo "    new Get{$feature}ByIdUseCase(\$${camelCase}Repo),\n";
    echo "    new Create{$feature}UseCase(\$${camelCase}Repo),\n";
    echo "    new Update{$feature}UseCase(\$${camelCase}Repo),\n";
    echo "    new Delete{$feature}UseCase(\$${camelCase}Repo),\n";
    echo ");\n";
}
