/**
 * Complete Swagger Annotations for SecureShare API
 * 
 * Copy these annotations to respective controllers
 */

// ===== PROJECT CONTROLLER ANNOTATIONS =====

/**
 * @OA\Post(
 *     path="/projects",
 *     tags={"Projects"},
 *     summary="Create new project",
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name"},
 *             @OA\Property(property="name", type="string", example="New Project"),
 *             @OA\Property(property="description", type="string")
 *         )
 *     ),
 *     @OA\Response(response=201, description="Project created")
 * )
 */
// For: public function store(Request $request)

/**
 * @OA\Get(
 *     path="/projects/{id}",
 *     tags={"Projects"},
 *     summary="Get project details",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Project details")
 * )
 */
// For: public function show($id)

/**
 * @OA\Put(
 *     path="/projects/{id}",
 *     tags={"Projects"},
 *     summary="Update project",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\RequestBody(
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="description", type="string")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Project updated")
 * )
 */
// For: public function update(Request $request, $id)

/**
 * @OA\Delete(
 *     path="/projects/{id}",
 *     tags={"Projects"},
 *     summary="Delete project",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Project deleted")
 * )
 */
// For: public function destroy($id)

// ===== DOCUMENT CONTROLLER ANNOTATIONS =====

/**
 * @OA\Get(
 *     path="/projects/{projectId}/documents",
 *     tags={"Documents"},
 *     summary="List project documents",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(name="projectId", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Documents list")
 * )
 */
// For: public function index(Request $request, $projectId)

/**
 * @OA\Get(
 *     path="/documents/{id}/download",
 *     tags={"Documents"},
 *     summary="Download encrypted document",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="File download")
 * )
 */
// For: public function download(Request $request, $id)

// ===== TASK CONTROLLER ANNOTATIONS =====

/**
 * @OA\Get(
 *     path="/projects/{projectId}/tasks",
 *     tags={"Tasks"},
 *     summary="List project tasks",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(name="projectId", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Tasks list")
 * )
 */
// For: public function index(Request $request, $projectId)

/**
 * @OA\Post(
 *     path="/projects/{projectId}/tasks",
 *     tags={"Tasks"},
 *     summary="Create new task",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(name="projectId", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"title"},
 *             @OA\Property(property="title", type="string"),
 *             @OA\Property(property="description", type="string"),
 *             @OA\Property(property="status", type="string", enum={"todo", "in_progress", "done"}),
 *             @OA\Property(property="priority", type="string", enum={"low", "medium", "high"}),
 *             @OA\Property(property="assigned_to", type="integer"),
 *             @OA\Property(property="deadline", type="string", format="date")
 *         )
 *     ),
 *     @OA\Response(response=201, description="Task created")
 * )
 */
// For: public function store(Request $request, $projectId)

// ===== CALENDAR CONTROLLER ANNOTATIONS =====

/**
 * @OA\Get(
 *     path="/calendar/events",
 *     tags={"Calendar"},
 *     summary="Get all calendar events",
 *     security={{"sanctum":{}}},
 *     @OA\Response(response=200, description="Events list")
 * )
 */
// For: public function getEvents(Request $request)

/**
 * @OA\Get(
 *     path="/calendar/month/{year}/{month}",
 *     tags={"Calendar"},
 *     summary="Get events for specific month",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(name="year", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="month", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Monthly events")
 * )
 */
// For: public function getMonthView(Request $request, $year, $month)

/**
 * @OA\Post(
 *     path="/milestones",
 *     tags={"Calendar"},
 *     summary="Create milestone",
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"project_id", "title", "type", "scheduled_date"},
 *             @OA\Property(property="project_id", type="integer"),
 *             @OA\Property(property="title", type="string"),
 *             @OA\Property(property="description", type="string"),
 *             @OA\Property(property="type", type="string", enum={"deadline", "meeting", "review", "launch", "milestone"}),
 *             @OA\Property(property="scheduled_date", type="string", format="date"),
 *             @OA\Property(property="scheduled_time", type="string", format="time")
 *         )
 *     ),
 *     @OA\Response(response=201, description="Milestone created")
 * )
 */
// For: public function storeMilestone(Request $request)

//  ===== NOTIFICATION CONTROLLER ANNOTATIONS =====

/**
 * @OA\Get(
 *     path="/notifications",
 *     tags={"Notifications"},
 *     summary="Get user notifications",
 *     security={{"sanctum":{}}},
 *     @OA\Response(response=200, description="Notifications list")
 * )
 */
// For: public function index(Request $request)

/**
 * @OA\Post(
 *     path="/notifications/{id}/read",
 *     tags={"Notifications"},
 *     summary="Mark notification as read",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Notification marked as read")
 * )
 */
// For: public function markAsRead($id)

// ===== AUDIT LOG CONTROLLER ANNOTATIONS =====

/**
 * @OA\Get(
 *     path="/audit-logs",
 *     tags={"Audit Logs"},
 *     summary="Get audit logs",
 *     security={{"sanctum":{}}},
 *     @OA\Response(response=200, description="Audit logs list")
 * )
 */
// For: public function index(Request $request)

// ===== USER CONTROLLER ANNOTATIONS =====

/**
 * @OA\Get(
 *     path="/users",
 *     tags={"Users"},
 *     summary="List all users",
 *     security={{"sanctum":{}}},
 *     @OA\Response(response=200, description="Users list")
 * )
 */
// For: public function index(Request $request)
