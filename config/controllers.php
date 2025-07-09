<?php

use Infrastructure\Database\EntityManagerFactory;
use Infrastructure\Persistence\Doctrine\{
    DesignerRepository,
    ClientRepository,
    UserRepository,
    TimelineRepository,
    AuthRepository,
    ServiceMasterRepository,
    TaskMessageRepository,
};
use Interface\Controller\{
    DesignerController,
    ClientController,
    UserController,
    TimelineController,
    AuthController,
    ServiceMasterController,
    TaskMessageController,
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
use Application\UseCase\ServiceMaster\{
    GetAllServiceMastersUseCase,
    GetServiceMasterByIdUseCase,
    CreateServiceMasterUseCase,
    UpdateServiceMasterUseCase,
    DeleteServiceMasterUseCase
};
use Application\UseCase\TaskMessage\{
    GetAllTaskMessagesUseCase,
    GetAllTaskMessagesByTaskIdUseCase,
    GetTaskMessageByIdUseCase,
    CreateTaskMessageUseCase,
    UpdateTaskMessageUseCase,
    DeleteTaskMessageUseCase
};

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
$serviceMasterRepo = new ServiceMasterRepository($em);
$taskMessageRepo = new TaskMessageRepository($em);

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

$serviceMasterController = new ServiceMasterController(
    new GetAllServiceMastersUseCase($serviceMasterRepo),
    new GetServiceMasterByIdUseCase($serviceMasterRepo),
    new CreateServiceMasterUseCase($serviceMasterRepo),
    new UpdateServiceMasterUseCase($serviceMasterRepo),
    new DeleteServiceMasterUseCase($serviceMasterRepo),
);

$taskMessageController = new TaskMessageController(
    new GetAllTaskMessagesUseCase($taskMessageRepo),
    new GetAllTaskMessagesByTaskIdUseCase($taskMessageRepo),
    new GetTaskMessageByIdUseCase($taskMessageRepo),
    new CreateTaskMessageUseCase($taskMessageRepo),
    new UpdateTaskMessageUseCase($taskMessageRepo),
    new DeleteTaskMessageUseCase($taskMessageRepo),
);
