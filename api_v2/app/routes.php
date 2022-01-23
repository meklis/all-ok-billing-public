<?php
declare(strict_types=1);

use Api\V2\Actions\Omo\Device\GetDeviceAction;
use Api\V2\Actions\Omo\Device\UpdateDeviceAction;
use Api\V2\Actions\Omo\User\RevokeDevice;
use Api\V2\Actions\Omo\User\ShareDevice;
use Api\V2\Actions\Priv\Customers\Prices\GetPricesListAction;
use Api\V2\Actions\Priv\Customers\Question\Report\SignCertOfCompletion;
use Api\V2\Actions\Priv\Customers\Question\SearchQuestionAction;
use Api\V2\Actions\Priv\Equipment\Binding\AddBindingAction;
use Api\V2\Actions\Priv\Equipment\Binding\DeleteBindingAction;
use Api\V2\Actions\Priv\Equipment\Binding\GetBindingAction;
use Api\V2\Actions\Priv\Equipment\Binding\UpdateBindingAction;
use Api\V2\Actions\Priv\Equipment\Device\FindDeviceAction;
use Api\V2\Actions\Priv\Equipment\Pinger\GetHostListAction;
use Api\V2\Actions\Priv\Equipment\Pinger\UpdateHostStatusAction;
use Api\V2\Actions\Pub\TrinityPlaylistAlias;
use Api\V2\Actions\StubPage\SearchPortAction;
use Api\V2\Actions\StubPage\UpdateMacAction;
use Api\V2\Actions\SwitcherCore\Switcher\CallModuleAction;
use Api\V2\Actions\SwitcherCore\Switcher\GetDeviceInfoAction;
use Api\V2\Actions\SwitcherCore\Switcher\GetDeviceStoreInfoAction;
use Api\V2\Actions\SwitcherCore\Switcher\GetModulesAction;
use Api\V2\Actions\User\AuthUserAction;
use Api\V2\Middleware\AuthByIpMiddleware;
use Api\V2\Middleware\AuthTokenMiddleware;
use Api\V2\Middleware\RealAddressMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Api\V2\Actions\Priv\Equipment\Radius\GetRadiusInfoAction;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use \Api\V2\Actions\Priv\Customers\ExtraContacts\ContactsGetAllAction;
use \Api\V2\Actions\Priv\Customers\ExtraContacts\ContactsAddAction;
use \Api\V2\Actions\Priv\Customers\ExtraContacts\ContactsUpdateAction;
use \Api\V2\Actions\Priv\Customers\ExtraContacts\ContactsDeleteAction;
use \Api\V2\Actions\Priv\Customers\CustomerInfoAction;
use \Api\V2\Actions\Priv\Equipment\Radius\PostAuthAction;

return function (App $app) {


    $app->group('/users', function (Group $group) {
        $group->post('/auth', AuthUserAction::class);
    });

    //Public routes
    $app->group('/v2/public', function (Group $group) {
        $group->group('/stub_page', function (Group $group) {
            $group->get('', SearchPortAction::class);
            $group->put('/{uuid}', UpdateMacAction::class);
        });
    })->add(RealAddressMiddleware::class);
    $app->get('/playlist/{playlist_id}', TrinityPlaylistAlias::class);

    //Private routes
    $app->group('/v2/private', function (Group $group) {
        $group->group('/switcher', function (Group $group) {
            $group->get('/device_info', GetDeviceInfoAction::class);
            $group->get('/device_store_info', GetDeviceStoreInfoAction::class);
            $group->get('/modules', GetModulesAction::class);
            $group->get('/module/{module}', CallModuleAction::class);
        });

        $group->group('/customers', function (Group $group) {
            $group->group('/activation', function (Group $group) {
               $group->get('', \Api\V2\Actions\Priv\Customers\Activations\GetActivationsActions::class) ;
               $group->get('/prices', \Api\V2\Actions\Priv\Customers\Activations\GetActivationPricesActions::class) ;
               $group->post('/activate', \Api\V2\Actions\Priv\Customers\Activations\CreateActivationAction::class) ;
               $group->post('/deactivate', \Api\V2\Actions\Priv\Customers\Activations\DeactivateActivationAction::class) ;
               $group->post('/frost', \Api\V2\Actions\Priv\Customers\Activations\FrostActivationAction::class) ;
               $group->post('/defrost', \Api\V2\Actions\Priv\Customers\Activations\DefrostActivationAction::class) ;
            });
            $group->group('/price', function (Group $group) {
                $group->get('/list', GetPricesListAction::class);
            });
            $group->group('/contacts', function (Group $group) {
                $group->get('', ContactsGetAllAction::class);
                $group->post('', ContactsAddAction::class);
                $group->put('/{id}', ContactsUpdateAction::class);
                $group->delete('/{id}', ContactsDeleteAction::class);
            });
            $group->group('/question', function (Group $group) {
                //Work with questions
                $group->post('', \Api\V2\Actions\Priv\Customers\Question\CreateQuestionAction::class);
                $group->get('/{id}/get', \Api\V2\Actions\Priv\Customers\Question\GetQuestionDetailAction::class);

                //Work with question updates
                $group->get('/{id}/updates', \Api\V2\Actions\Priv\Customers\Question\GetQuestionCommentsAction::class);
                $group->post('/{id}/update', \Api\V2\Actions\Priv\Customers\Question\AddQuestionCommentAction::class);

                //Work with reports
                $group->get('/{id}/reports', \Api\V2\Actions\Priv\Customers\Question\GetQuestionReportsAction::class);
                $group->post('/{id}/report', \Api\V2\Actions\Priv\Customers\Question\AddQuestionReportAction::class);

                //General methods
                $group->post('/report/sign_cert_of_completion', SignCertOfCompletion::class);
                $group->get('/search', SearchQuestionAction::class);
                $group->get('/reasons', \Api\V2\Actions\Priv\Customers\Question\GetQuestionReasonsAction::class);
            });
            $group->get('/info/{id}', CustomerInfoAction::class);
            $group->put('/info/{id}', \Api\V2\Actions\Priv\Customers\CustomerUpdateAction::class);
            $group->get('/search', \Api\V2\Actions\Priv\Customers\CustomerSearchAction::class);
        });

        $group->group('/addresses', function (Group $group) {
           $group->get('/cities', \Api\V2\Actions\Priv\Addresses\GetCitiesActions::class);
           $group->get('/streets', \Api\V2\Actions\Priv\Addresses\GetStreetsAction::class);
           $group->get('/houses', \Api\V2\Actions\Priv\Addresses\GetHousesAction::class);
           $group->get('/groups', \Api\V2\Actions\Priv\Addresses\GetGroupsAction::class);
           $group->get('/house/{id}', \Api\V2\Actions\Priv\Addresses\GetHouseInfoAction::class);
        });

        $group->group('/equipment', function (Group $group) {
            $group->group('/binding', function (Group $group) {
                $group->post('', AddBindingAction::class);
                $group->get('/{id}', GetBindingAction::class);
                $group->delete('/{id}', DeleteBindingAction::class);
                $group->put('/{id}', UpdateBindingAction::class);
            });
            $group->group('/stub_page', function (Group $group) {
                $group->get('', SearchPortAction::class);
                $group->put('/{uuid}', UpdateMacAction::class);
            });
            $group->group('/device', function (Group $group) {
                $group->get('/find', FindDeviceAction::class);
            });
        });

        $group->group('/employees', function (Group $group) {
            $group->get('/status', \Api\V2\Actions\User\GetUserStatusAction::class);
            $group->post('/status', \Api\V2\Actions\User\SetUserStatusAction::class);
            $group->get('/responsible_list', \Api\V2\Actions\Priv\Employees\GetAllResponsibleEmployeeAction::class);
            $group->group('/schedule', function (Group $group) {
               $group->post('', \Api\V2\Actions\Priv\Schedule\CreateScheduleAction::class);
               $group->delete('/{id}', \Api\V2\Actions\Priv\Schedule\DeleteScheduleAction::class);
               $group->put('/{id}', \Api\V2\Actions\Priv\Schedule\UpdateScheduleAction::class);
               $group->get('', \Api\V2\Actions\Priv\Schedule\GetSchedulesByPeriodAction::class);
               $group->get('/{id}', \Api\V2\Actions\Priv\Schedule\GetScheduleAction::class);
            });
            $group->get('/schedule_types', \Api\V2\Actions\Priv\Schedule\GetScheduleCalendarTypes::class);
            $group->get('/schedule_groups', \Api\V2\Actions\Priv\Schedule\GetScheduleGroups::class);
            $group->group('/slots', function (Group $group) {
                $group->get('/range', \Api\V2\Actions\Priv\Schedule\TimeSlots\GetSlotsAction::class);
                $group->get('/raw', \Api\V2\Actions\Priv\Schedule\TimeSlots\GetRawSlotsAction::class);
            });
        });
    })->add(RealAddressMiddleware::class)->add(AuthTokenMiddleware::class);

    //trusted by ip routes
    $app->group('/v2/trusted', function (Group $group) {
        $group->group('/equipment', function (Group $group) {
            $group->group('/pinger', function (Group $group) {
                $group->post('', UpdateHostStatusAction::class);
                $group->get('', GetHostListAction::class);
            });
            $group->group('/radius', function (Group $group) {
                $group->post('/request', GetRadiusInfoAction::class);
                $group->post('/post-auth', PostAuthAction::class);
                $group->post('/acct', \Api\V2\Actions\Priv\Equipment\Radius\AcctAction::class);
            });
            $group->group('/switcher', function (Group $group) {
                $group->get('/device_info', GetDeviceInfoAction::class);
                $group->get('/device_store_info', GetDeviceStoreInfoAction::class);
                $group->get('/modules', GetModulesAction::class);
                $group->get('/module/{module}', CallModuleAction::class);
            });
        });
    })->add(RealAddressMiddleware::class)->add(AuthByIpMiddleware::class);


    $app->group('/omo', function (Group $group) {
        $group->get('/device/{id}', GetDeviceAction::class);
        $group->post('/device/{id}', UpdateDeviceAction::class);
        $group->post('/user/share', ShareDevice::class);
        $group->post('/user/revoke', RevokeDevice::class);
    })->add(AuthTokenMiddleware::class);

    $app->options('/{routes:.+}', function (Request $request, Response $response) {
        return $response;
    });
    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function (Request $request, Response $response) {
        throw new HttpNotFoundException($request);
    });
};
