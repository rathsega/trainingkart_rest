<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->post('/Auth/validateUser', 'Auth::validateUser');
$routes->get('/Blogs/getPaginatedBlogs/(:any)?/(:any)?', 'Blogs::getPaginatedBlogs/$1/$2');
$routes->get('/Blogs/getPaginatedInterviewQuestions/(:any)?/(:any)?', 'Blogs::getPaginatedInterviewQuestions/$1/$2');
$routes->get('/Blogs/getBlogsCount', 'Blogs::getBlogsCount');
$routes->get('/Blogs/getInterviewQuestionsCount', 'Blogs::getInterviewQuestionsCount');
$routes->get('/Blogs/getUniqueKeywords', 'Blogs::getUniqueKeywords');
$routes->get('/Blogs/getRecentTagsOfBlogs', 'Blogs::getRecentTagsOfBlogs');
$routes->get('/Blogs/getRecentTagsOfInterviews', 'Blogs::getRecentTagsOfInterviews');
$routes->get('/Blogs/getCategoriesWithBlogCount', 'Blogs::getCategoriesWithBlogCount');
$routes->get('/Blogs/getCategoriesWithInterviewQuestionCount', 'Blogs::getCategoriesWithInterviewQuestionCount');
$routes->get('/Home/getUsersCount', 'Home::getUsersCount');
$routes->get('/Home/getRatingsCount', 'Home::getRatingsCount');
$routes->get('/Home/getCourseCount', 'Home::getCourseCount');
$routes->get('/Home/getCategories', 'Home::getCategories');
$routes->get('/Home/getCoursesList/(:segment)', 'Home::getCoursesList/$1');
$routes->get('/Home/getAllInstructors', 'Home::getAllInstructors');
$routes->post('/Register/register', 'Register::register');
$routes->post('/auth/authenticate', 'Auth::authenticate');
$routes->get('/auth/validateToken', 'Auth::validateToken');
$routes->get('/auth/logout', 'Auth::logout');
$routes->post('/auth/sendResetLink', 'Auth::sendResetLink');
$routes->post('/auth/updatePassword', 'Auth::updatePassword');
$routes->post('/file/upload', 'FileController::upload');
$routes->delete('/file/delete', 'FileController::deleteFile');
$routes->get('/user/getProfile', 'User::getProfile');
$routes->post('/user/updateProfile', 'User::updateProfile');
$routes->post('/user/changePassword', 'User::changePassword');
$routes->post('/user/uploadAvatar', 'User::uploadAvatar');
$routes->delete('/user/deleteAvatar', 'User::deleteAvatar');
$routes->get('orders/getPurchaseHistory', 'Orders::getPurchaseHistory');
$routes->get('enrol/getEnrolments', 'Enrol::getEnrolments');
$routes->get('Course/getCourseDetails/(:any)/(:any)/(:any)/(:any)', 'Course::getCourseDetails/$1/$2/$3/$4');
$routes->get('Course/getCourseDetails/(:any)/(:any)/(:any)', 'Course::getCourseDetails/$1/$2/$3');
$routes->get('Course/getCourseDetails/(:any)/(:any)', 'Course::getCourseDetails/$1/$2');
$routes->get('Course/getCourseDetails/(:any)', 'Course::getCourseDetails/$1');
$routes->get('Course/getHomePageCourses', 'Course::getHomePageCourses');
$routes->get('Course/getFooterCourses', 'Course::getFooterCourses');
$routes->post('Course/getCourseById', 'Course::getCourseById');
$routes->post('Course/getAllCourses', 'Course::getAllCourses');
$routes->post('demoRequests/requestDemo', 'DemoRequests::requestDemo');
$routes->post('demoRequests/requestCall', 'DemoRequests::requestCall');
$routes->post('contact/submitted', 'Contact::contactus_submitted');
$routes->post('wishlist/add', 'Wishlist::add');
$routes->get('wishlist', 'Wishlist::getWishlist');
$routes->delete('wishlist/(:num)', 'Wishlist::remove/$1');
$routes->options('(:any)', 'Blogs::options');
// $routes->resource('jobs');
$routes->get('jobs/getPaginatedJobs/(:any)?/(:any)?', 'Jobs::getPaginatedJobs/$1/$2');
$routes->get('jobs', 'Jobs::index');
$routes->get('jobs/show/(:any)?', 'Jobs::show/$1');
$routes->post('jobs/apply', 'Jobs::apply');
$routes->get('jobs/getFilterData', 'Jobs::getFilterData');
$routes->get('jobs/getJobsCount', 'Jobs::getJobsCount');
$routes->post('jobs/getJobsByUsingFilters', 'Jobs::getJobsByUsingFilters');
// $routes->post('api/users', 'API\UsersController::create');
// $routes->get('api/users/(:segment)', 'API\UsersController::show/$1');
// $routes->put('api/users/(:segment)', 'API\UsersController::update/$1');
// $routes->delete('api/users/(:segment)', 'API\UsersController::delete/$1');



// $routes->group('', ['filter' => 'cors'], function($routes) {
//     $routes->get('/', 'Home::index');
//     $routes->post('/Auth/validateUser', 'Auth::validateUser');
//     $routes->get('/Blogs/getPaginatedBlogs', 'Blogs::getPaginatedBlogs');
// });


$routes->get('Course/getCourseCurriculum/(:any)?', 'Course::getCourseCurriculum/$1');
$routes->get('Course/getCourseDetailsOfRequiredData/(:any)/(:any)', 'Course::getCourseDetailsOfRequiredData/$1/$2');
$routes->get('Course/getRelatedCourses/(:any)/(:any)/(:any)', 'Course::getRelatedCourses/$1/$2/$3');