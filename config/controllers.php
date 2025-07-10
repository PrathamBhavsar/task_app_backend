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
    TaskRepository,
    MeasurementRepository,
    ServiceRepository,
    QuoteRepository
};
use Interface\Controller\{
    DesignerController,
    ClientController,
    UserController,
    TimelineController,
    AuthController,
    ServiceMasterController,
    TaskMessageController,
    TaskController,
    MeasurementController,
    ServiceController,
    QuoteController
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
use Application\UseCase\Task\{
    GetAllTasksUseCase,
    GetTaskByIdUseCase,
    CreateTaskUseCase,
    UpdateTaskUseCase,
    DeleteTaskUseCase
};
use Application\UseCase\Measurement\{
    GetAllMeasurementsUseCase,
    GetAllMeasurementsByTaskIdUseCase,
    GetMeasurementByIdUseCase,
    CreateMeasurementUseCase,
    UpdateMeasurementUseCase,
    DeleteMeasurementUseCase
};
use Application\UseCase\Service\{
    GetAllServicesUseCase,
    GetAllServicesByTaskIdUseCase,
    GetServiceByIdUseCase,
    CreateServiceUseCase,
    UpdateServiceUseCase,
    DeleteServiceUseCase
};
use Application\UseCase\Quote\{
    GetAllQuotesUseCase,
    GetQuoteByIdUseCase,
    CreateQuoteUseCase,
    UpdateQuoteUseCase,
    DeleteQuoteUseCase
};

$em = EntityManagerFactory::create();



$designerRepo = new DesignerRepository($em);
$clientRepo = new ClientRepository($em);
$userRepo = new UserRepository($em);
$timelineRepo = new TimelineRepository($em);
$serviceMasterRepo = new ServiceMasterRepository($em);
$taskMessageRepo = new TaskMessageRepository($em);
$taskRepo = new TaskRepository($em);
$measurementRepo = new MeasurementRepository($em);
$serviceRepo = new ServiceRepository($em);
$quoteRepo = new QuoteRepository($em);

$designerController = new DesignerController(
    new GetAllDesignersUseCase($designerRepo),
    new GetDesignerByIdUseCase($designerRepo),
    new CreateDesignerUseCase($designerRepo),
    new UpdateDesignerUseCase($designerRepo),
    new DeleteDesignerUseCase($designerRepo),
);

$clientController = new ClientController(
    new GetAllClientsUseCase($clientRepo),
    new GetClientByIdUseCase($clientRepo),
    new CreateClientUseCase($clientRepo),
    new UpdateClientUseCase($clientRepo),
    new DeleteClientUseCase($clientRepo),
);

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

$taskController = new TaskController(
    new GetAllTasksUseCase($taskRepo),
    new GetTaskByIdUseCase($taskRepo),
    new CreateTaskUseCase($taskRepo),
    new UpdateTaskUseCase($taskRepo),
    new DeleteTaskUseCase($taskRepo),
);

$measurementController = new MeasurementController(
    new GetAllMeasurementsUseCase($measurementRepo),
    new GetAllMeasurementsByTaskIdUseCase($measurementRepo),
    new GetMeasurementByIdUseCase($measurementRepo),
    new CreateMeasurementUseCase($measurementRepo),
    new UpdateMeasurementUseCase($measurementRepo),
    new DeleteMeasurementUseCase($measurementRepo),
);

$serviceController = new ServiceController(
    new GetAllServicesUseCase($serviceRepo),
    new GetAllServicesByTaskIdUseCase($serviceRepo),
    new GetServiceByIdUseCase($serviceRepo),
    new CreateServiceUseCase($serviceRepo),
    new UpdateServiceUseCase($serviceRepo),
    new DeleteServiceUseCase($serviceRepo),
);

$quoteController = new QuoteController(
    new GetAllQuotesUseCase($quoteRepo),
    new GetQuoteByIdUseCase($quoteRepo),
    new CreateQuoteUseCase($quoteRepo),
    new UpdateQuoteUseCase($quoteRepo),
    new DeleteQuoteUseCase($quoteRepo),
);
