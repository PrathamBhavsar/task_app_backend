<?php

use Infrastructure\Database\EntityManagerFactory;
use Infrastructure\Persistence\Doctrine\{
    DesignerRepository,
    ClientRepository,
    UserRepository,
    TimelineRepository,
    AuthRepository
};
use Interface\Controller\{
    DesignerController,
    ClientController,
    UserController,
    TimelineController,
    AuthController
};
use Application\UseCase\Designer\{
    GetAllDesignersUseCase,
    GetDesignerByIdUseCase,
    CreateDesignerUseCase,
    UpdateDesignerUseCase,
    DeleteDesignerUseCase
};
use Application\UseCase\Client\{
    GetAllClientsUseCase,
    GetClientByIdUseCase,
    CreateClientUseCase,
    UpdateClientUseCase,
    DeleteClientUseCase
};
use Application\UseCase\User\{
    GetAllUsersUseCase,
    GetUserByIdUseCase,
    CreateUserUseCase,
    UpdateUserUseCase,
    DeleteUserUseCase
};
use Application\UseCase\Timeline\{
    GetAllTimelinesUseCase,
    GetAllTimelinesByTaskIdUseCase,
    GetTimelineByIdUseCase,
    CreateTimelineUseCase,
    UpdateTimelineUseCase,
    DeleteTimelineUseCase
};
use Application\UseCase\Auth\{LoginUseCase, RegisterUseCase};

$em = EntityManagerFactory::create();

$designerController = new DesignerController(
    new GetAllDesignersUseCase(new DesignerRepository($em)),
    new GetDesignerByIdUseCase(new DesignerRepository($em)),
    new CreateDesignerUseCase(new DesignerRepository($em)),
    new UpdateDesignerUseCase(new DesignerRepository($em)),
    new DeleteDesignerUseCase(new DesignerRepository($em))
);

$clientController = new ClientController(
    new GetAllClientsUseCase(new ClientRepository($em)),
    new GetClientByIdUseCase(new ClientRepository($em)),
    new CreateClientUseCase(new ClientRepository($em)),
    new UpdateClientUseCase(new ClientRepository($em)),
    new DeleteClientUseCase(new ClientRepository($em))
);

$userRepo = new UserRepository($em);
$timelineRepo = new TimelineRepository($em);
$timelineController = new TimelineController(
    new GetAllTimelinesUseCase($timelineRepo),
    new GetAllTimelinesByTaskIdUseCase($timelineRepo),
    new GetTimelineByIdUseCase($timelineRepo),
    new CreateTimelineUseCase($timelineRepo, $userRepo),
    new UpdateTimelineUseCase($timelineRepo, $userRepo),
    new DeleteTimelineUseCase($timelineRepo)
);

$userController = new UserController(
    new GetAllUsersUseCase($userRepo),
    new GetUserByIdUseCase($userRepo),
    new CreateUserUseCase($userRepo),
    new UpdateUserUseCase($userRepo),
    new DeleteUserUseCase($userRepo)
);

$authController = new AuthController(
    new LoginUseCase(new AuthRepository($em)),
    new RegisterUseCase(new AuthRepository($em))
);
