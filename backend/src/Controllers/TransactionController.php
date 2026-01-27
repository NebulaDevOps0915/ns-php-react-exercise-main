<?php

namespace App\Controllers;

use App\Models\Transaction;
use App\Models\Category;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TransactionController
{
    private EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function index(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $skip = isset($params['skip']) ? (int)$params['skip'] : 0;
        $limit = isset($params['limit']) ? (int)$params['limit'] : 100;

        $transactionRepo = $this->em->getRepository(Transaction::class);

        $transactions = $transactionRepo->createQueryBuilder('t')
            ->leftJoin('t.category', 'c')
            ->addSelect('c')
            ->setFirstResult($skip)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $data = array_map(fn($t) => $t->toArray(), $transactions);

        $response->getBody()->write(json_encode($data, JSON_PRESERVE_ZERO_FRACTION));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];

        $transactionRepo = $this->em->getRepository(Transaction::class);

        $transaction = $transactionRepo->createQueryBuilder('t')
            ->leftJoin('t.category', 'c')
            ->addSelect('c')
            ->where('t.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$transaction) {
            $error = ['detail' => 'Transaction not found'];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $response->getBody()->write(json_encode($transaction->toArray(), JSON_PRESERVE_ZERO_FRACTION));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function create(Request $request, Response $response): Response
    {
        $data = json_decode($request->getBody()->getContents(), true);

        if (
            empty($data['description']) ||
            !isset($data['amount']) ||
            empty($data['type']) ||
            !isset($data['category_id']) ||
            !isset($data['user_id'])
        ) {
            $error = ['detail' => 'Missing required fields'];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(422);
        }

        $categoryRepo = $this->em->getRepository(Category::class);
        $category = $categoryRepo->find($data['category_id']);

        if (!$category) {
            $error = ['detail' => 'Category not found'];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(422);
        }

        $transaction = new Transaction();
        $transaction->setDescription($data['description']);
        $transaction->setAmount((float)$data['amount']);
        $transaction->setType($data['type']);
        $transaction->setCategory($category);
        $transaction->setUserId((int)$data['user_id']);

        if (isset($data['date'])) {
            $transaction->setDate(new \DateTime($data['date']));
        }

        $this->em->persist($transaction);
        $this->em->flush();

        $response->getBody()->write(json_encode($transaction->toArray(), JSON_PRESERVE_ZERO_FRACTION));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $data = json_decode($request->getBody()->getContents(), true);

        $transactionRepo = $this->em->getRepository(Transaction::class);
        $transaction = $transactionRepo->find($id);

        if (!$transaction) {
            $error = ['detail' => 'Transaction not found'];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        if (isset($data['description'])) {
            $transaction->setDescription($data['description']);
        }
        if (isset($data['amount'])) {
            $transaction->setAmount((float)$data['amount']);
        }
        if (isset($data['type'])) {
            $transaction->setType($data['type']);
        }
        if (isset($data['category_id'])) {
            $categoryRepo = $this->em->getRepository(Category::class);
            $category = $categoryRepo->find($data['category_id']);
            if ($category) {
                $transaction->setCategory($category);
            }
        }
        if (isset($data['user_id'])) {
            $transaction->setUserId((int)$data['user_id']);
        }

        $this->em->flush();

        $this->em->refresh($transaction);

        $response->getBody()->write(json_encode($transaction->toArray(), JSON_PRESERVE_ZERO_FRACTION));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];

        $transactionRepo = $this->em->getRepository(Transaction::class);

        $transaction = $transactionRepo->createQueryBuilder('t')
            ->leftJoin('t.category', 'c')
            ->addSelect('c')
            ->where('t.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$transaction) {
            $error = ['detail' => 'Transaction not found'];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $data = $transaction->toArray();

        $this->em->remove($transaction);
        $this->em->flush();

        $response->getBody()->write(json_encode($data, JSON_PRESERVE_ZERO_FRACTION));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function grid(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $page = isset($params['page']) ? max(1, (int)$params['page']) : 1;
        $size = isset($params['size']) ? max(1, (int)$params['size']) : 10;
        $sortBy = $params['sort_by'] ?? 'date';
        $sortOrder = strtolower($params['sort_order'] ?? 'desc');

        // Validate sort_order
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        // Validate sort_by column to prevent SQL injection and map to actual column name
        $sortColumnMap = [
            'date' => 't.date',
            'description' => 't.description',
            'amount' => 't.amount',
            'type' => 't.type',
            'category_id' => 't.category_id',
        ];
        $sortColumn = $sortColumnMap[$sortBy] ?? 't.date';

        $offset = ($page - 1) * $size;

        $conn = $this->em->getConnection();

        // Get total count
        $countSql = "
            SELECT COUNT(DISTINCT t.id) as total
            FROM transactions t
            LEFT JOIN categories c ON t.category_id = c.id
        ";
        $totalResult = $conn->fetchAssociative($countSql);
        $total = (int)$totalResult['total'];

        // Get paginated and sorted data with tags
        $sql = "
            SELECT 
                t.id,
                t.description,
                t.amount,
                t.type,
                t.category_id,
                t.user_id,
                t.date,
                c.id as category_id_rel,
                c.name as category_name,
                COALESCE(
                    JSON_AGG(
                        JSON_BUILD_OBJECT('id', tag.id, 'name', tag.name)
                    ) FILTER (WHERE tag.id IS NOT NULL),
                    '[]'::json
                ) as tags
            FROM transactions t
            LEFT JOIN categories c ON t.category_id = c.id
            LEFT JOIN transaction_tags tt ON t.id = tt.transaction_id
            LEFT JOIN tags tag ON tt.tag_id = tag.id
            GROUP BY t.id, t.description, t.amount, t.type, t.category_id, t.user_id, t.date, c.id, c.name
            ORDER BY " . $sortColumn . " " . strtoupper($sortOrder) . "
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('limit', $size, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $result = $stmt->executeQuery();
        $rows = $result->fetchAllAssociative();

        // Format the response
        $items = [];
        foreach ($rows as $row) {
            $tags = json_decode($row['tags'], true);
            if (!is_array($tags)) {
                $tags = [];
            }

            $items[] = [
                'id' => (int)$row['id'],
                'description' => $row['description'],
                'amount' => (float)$row['amount'],
                'type' => $row['type'],
                'category_id' => (int)$row['category_id'],
                'user_id' => (int)$row['user_id'],
                'date' => (new \DateTime($row['date']))->format('Y-m-d\TH:i:s\Z'),
                'category' => [
                    'id' => (int)$row['category_id_rel'],
                    'name' => $row['category_name'],
                ],
                'tags' => $tags,
            ];
        }

        $responseData = [
            'items' => $items,
            'total' => $total,
        ];

        $response->getBody()->write(json_encode($responseData, JSON_PRESERVE_ZERO_FRACTION));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
