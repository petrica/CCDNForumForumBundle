<?php

/*
 * This file is part of the CCDNForum ForumBundle
 *
 * (c) CCDN (c) CodeConsortium <http://www.codeconsortium.com/>
 *
 * Available on github <http://www.github.com/codeconsortium/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CCDNForum\ForumBundle\Model\Repository;

use Symfony\Component\Security\Core\User\UserInterface;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;

use CCDNForum\ForumBundle\Model\Repository\BaseRepository;
use CCDNForum\ForumBundle\Model\Repository\BaseRepositoryInterface;

/**
 * TopicRepository
 *
 * @category CCDNForum
 * @package  ForumBundle
 *
 * @author   Reece Fowell <reece@codeconsortium.com>
 * @license  http://opensource.org/licenses/MIT MIT
 * @version  Release: 2.0
 * @link     https://github.com/codeconsortium/CCDNForumForumBundle
 */
class TopicRepository extends BaseRepository implements BaseRepositoryInterface
{
    /**
     *
     * @access public
     * @return bool
     */
    public function allowedToViewDeletedTopics()
    {
		return true;
        return $this->managerBag->getPolicyManager()->allowedToViewDeletedTopics();
    }

    /**
     *
     * @access public
     * @param  int                                 $boardId
     * @return \CCDNForum\ForumBundle\Entity\Topic
     */
    public function findLastTopicForBoardByIdWithLastPost($boardId)
    {
        if (null == $boardId || ! is_numeric($boardId) || $boardId == 0) {
            throw new \Exception('Board id "' . $boardId . '" is invalid!');
        }

        $params = array(':boardId' => $boardId);

        $qb = $this->createSelectQuery(array('t', 'fp', 'fp_author', 'lp', 'lp_author', 't_closedBy', 't_deletedBy', 't_stickiedBy'));

        $qb
            ->innerJoin('t.firstPost', 'fp')
                ->leftJoin('fp.createdBy', 'fp_author')
            ->leftJoin('t.lastPost', 'lp')
                ->leftJoin('lp.createdBy', 'lp_author')
            ->leftJoin('t.closedBy', 't_closedBy')
            ->leftJoin('t.deletedBy', 't_deletedBy')
            ->leftJoin('t.stickiedBy', 't_stickiedBy')
            ->where('t.board = :boardId')
            ->andWhere('t.isDeleted = FALSE')
            ->orderBy('lp.createdDate', 'DESC')
            ->setMaxResults(1);

        return $this->gateway->findTopic($qb, $params);
    }

    /**
     *
     * @access public
     * @param  int                                 $topicId
     * @return \CCDNForum\ForumBundle\Entity\Topic
     */
    public function findOneByIdWithPostsByTopicId($topicId)
    {
        if (null == $topicId || ! is_numeric($topicId) || $topicId == 0) {
            throw new \Exception('Topic id "' . $topicId . '" is invalid!');
        }

        $canViewDeleted = $this->allowedToViewDeletedTopics();

        $params = array(':topicId' => $topicId);

        $qb = $this->createSelectQuery(array('t', 'p', 'fp', 'lp', 'b', 'c', 'fp', 'fp_author', 'lp', 'lp_author', 't_closedBy', 't_deletedBy', 't_stickiedBy'));

        $qb
            ->innerJoin('t.firstPost', 'fp')
                ->leftJoin('fp.createdBy', 'fp_author')
            ->leftJoin('t.lastPost', 'lp')
                ->leftJoin('lp.createdBy', 'lp_author')
            ->leftJoin('t.closedBy', 't_closedBy')
            ->leftJoin('t.deletedBy', 't_deletedBy')
            ->leftJoin('t.stickiedBy', 't_stickiedBy')
            ->innerJoin('t.posts', 'p')
            ->leftJoin('t.board', 'b')
            ->leftJoin('b.category', 'c')
            ->where(
                $this->limitQueryByDeletedStateAndByTopicId($qb, $canViewDeleted)
            );

        return $this->gateway->findTopic($qb, $params);
    }

    /**
     *
     * @access public
     * @param  int                                 $topicId
     * @return \CCDNForum\ForumBundle\Entity\Topic
     */
    public function findOneByIdWithBoardAndCategory($topicId)
    {
        if (null == $topicId || ! is_numeric($topicId) || $topicId == 0) {
            throw new \Exception('Topic id "' . $topicId . '" is invalid!');
        }

        $canViewDeleted = $this->allowedToViewDeletedTopics();

        $params = array(':topicId' => $topicId);

        $qb = $this->createSelectQuery(array('t', 'b', 'c', 'fp', 'fp_author', 'lp', 'lp_author', 't_closedBy', 't_deletedBy', 't_stickiedBy'));

        $qb
            ->innerJoin('t.firstPost', 'fp')
                ->leftJoin('fp.createdBy', 'fp_author')
            ->leftJoin('t.lastPost', 'lp')
                ->leftJoin('lp.createdBy', 'lp_author')
            ->leftJoin('t.closedBy', 't_closedBy')
            ->leftJoin('t.deletedBy', 't_deletedBy')
            ->leftJoin('t.stickiedBy', 't_stickiedBy')
            ->leftJoin('t.board', 'b')
            ->leftJoin('b.category', 'c')
            ->where(
                $this->limitQueryByDeletedStateAndByTopicId($qb, $canViewDeleted)
            );

        return $this->gateway->findTopic($qb, $params);
    }

    /**
     *
     * @access public
     * @param  int                                          $boardId
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findAllStickiedByBoardId($boardId)
    {
        if (null == $boardId || ! is_numeric($boardId) || $boardId == 0) {
            throw new \Exception('Board id "' . $boardId . '" is invalid!');
        }

        $canViewDeleted = $this->allowedToViewDeletedTopics();

        $params = array(':boardId' => $boardId, ':isSticky' => true);

        $qb = $this->createSelectQuery(array('t', 'b', 'c', 'fp', 'fp_author', 'lp', 'lp_author', 't_closedBy', 't_deletedBy', 't_stickiedBy'));

        $qb
            ->innerJoin('t.firstPost', 'fp')
                ->leftJoin('fp.createdBy', 'fp_author')
            ->leftJoin('t.lastPost', 'lp')
                ->leftJoin('lp.createdBy', 'lp_author')
            ->leftJoin('t.closedBy', 't_closedBy')
            ->leftJoin('t.deletedBy', 't_deletedBy')
            ->leftJoin('t.stickiedBy', 't_stickiedBy')
            ->leftJoin('t.board', 'b')
            ->leftJoin('b.category', 'c')
            ->where(
                $this->limitQueryByStickiedAndDeletedStateByBoardId($qb, $canViewDeleted)
            )
            ->orderBy('lp.createdDate', 'DESC');

        return $this->gateway->findTopics($qb, $params);
    }

    /**
     *
     * @access public
     * @param  int                    $boardId
     * @param  int                    $page
     * @return \Pagerfanta\Pagerfanta
     */
    public function findAllPaginatedByBoardId($boardId, $page)
    {
        if (null == $boardId || ! is_numeric($boardId) || $boardId == 0) {
            throw new \Exception('Board id "' . $boardId . '" is invalid!');
        }

        $canViewDeleted = $this->allowedToViewDeletedTopics();

        $params = array(':boardId' => $boardId, ':isSticky' => false);

        $qb = $this->createSelectQuery(array('t', 'b', 'c', 'fp', 'fp_author', 'lp', 'lp_author', 't_closedBy', 't_deletedBy', 't_stickiedBy'));

        $qb
            ->innerJoin('t.firstPost', 'fp')
                ->leftJoin('fp.createdBy', 'fp_author')
            ->leftJoin('t.lastPost', 'lp')
                ->leftJoin('lp.createdBy', 'lp_author')
            ->leftJoin('t.closedBy', 't_closedBy')
            ->leftJoin('t.deletedBy', 't_deletedBy')
            ->leftJoin('t.stickiedBy', 't_stickiedBy')
            ->leftJoin('t.board', 'b')
            ->leftJoin('b.category', 'c')
            ->where(
                $this->limitQueryByStickiedAndDeletedStateByBoardId($qb, $canViewDeleted)
            )
            ->setParameters($params)
            ->orderBy('lp.createdDate', 'DESC');

        return $this->gateway->paginateQuery($qb, $this->getTopicsPerPageOnBoards(), $page);
    }

    /**
     *
     * @access protected
     * @param  \Doctrine\ORM\QueryBuilder $qb
     * @param  bool                       $canViewDeletedTopics
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function limitQueryByDeletedStateAndByTopicId(QueryBuilder $qb, $canViewDeletedTopics)
    {
        if ($canViewDeletedTopics) {
            $expr = $qb->expr()->eq('t.id', ':topicId');
        } else {
            $expr = $qb->expr()->andX(
                $qb->expr()->eq('t.id', ':topicId'),
                $qb->expr()->eq('t.isDeleted', 'FALSE')
            );
        }

        return $expr;
    }

    /**
     *
     * @access protected
     * @param  \Doctrine\ORM\QueryBuilder $qb
     * @param  bool                       $canViewDeletedTopics
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function limitQueryByStickiedAndDeletedStateByBoardId(QueryBuilder $qb, $canViewDeletedTopics)
    {
        if ($canViewDeletedTopics) {
            $expr = $qb->expr()->andX(
                $qb->expr()->eq('t.board', ':boardId'),
                $qb->expr()->eq('t.isSticky', ':isSticky')
            );
        } else {
            $expr = $qb->expr()->andX(
                $qb->expr()->eq('t.board', ':boardId'),
                $qb->expr()->andX(
                    $qb->expr()->eq('t.isSticky', ':isSticky'),
                    $qb->expr()->eq('t.isDeleted', 'FALSE')
                )
            );
        }

        return $expr;
    }

    /**
     *
     * @access public
     * @param  int   $boardId
     * @return Array
     */
    public function getPostCountForTopicById($topicId)
    {
        if (null == $topicId || ! is_numeric($topicId) || $topicId == 0) {
            throw new \Exception('Topic id "' . $topicId . '" is invalid!');
        }

        $qb = $this->getQueryBuilder();

        $topicEntityClass = $this->managerBag->getTopicManager()->getGateway()->getEntityClass();

        $qb
            ->select('COUNT(DISTINCT p.id) AS postCount')
            ->from($topicEntityClass, 't')
            ->leftJoin('t.posts', 'p')
            ->where('t.id = :topicId')
            ->setParameter(':topicId', $topicId);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return array('postCount' => null);
        } catch (\Exception $e) {
            return array('postCount' => null);
        }
    }

    /**
     *
     * @access public
     * @param  Array                                        $topicIds
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findTheseTopicsById($topicIds = array())
    {
        if (! is_array($topicIds) || count($topicIds) < 1) {
            throw new \Exception('Parameter 1 must be an array and contain at least 1 topic id!');
        }

        $qb = $this->createSelectQuery(array('t'));

        $qb->where($qb->expr()->in('t.id', $topicIds));

        return $this->gateway->findTopics($qb);
    }

    /**
     *
     * @access public
     * @param  int                    $page
     * @return \Pagerfanta\Pagerfanta
     */
    public function findClosedTopicsForModeratorsPaginated($page)
    {
        $params = array(':isClosed' => true);

        $qb = $this->createSelectQuery(array('t', 'b', 'c', 'fp', 'fp_author', 'lp', 'lp_author', 't_closedBy', 't_deletedBy', 't_stickiedBy'));

        $qb
            ->innerJoin('t.firstPost', 'fp')
                ->leftJoin('fp.createdBy', 'fp_author')
            ->leftJoin('t.lastPost', 'lp')
                ->leftJoin('lp.createdBy', 'lp_author')
            ->leftJoin('t.closedBy', 't_closedBy')
            ->leftJoin('t.deletedBy', 't_deletedBy')
            ->leftJoin('t.stickiedBy', 't_stickiedBy')
            ->leftJoin('t.board', 'b')
            ->leftJoin('b.category', 'c')
            ->where('t.isClosed = :isClosed')
            ->setParameters($params)
            ->orderBy('lp.createdDate', 'DESC');

        return $this->gateway->paginateQuery($qb, $this->getTopicsPerPageOnBoards(), $page);
    }

    /**
     *
     * @access public
     * @param  int                    $page
     * @return \Pagerfanta\Pagerfanta
     */
    public function findDeletedTopicsForAdminsPaginated($page)
    {
        $params = array(':isDeleted' => true);

        $qb = $this->createSelectQuery(array('t', 'b', 'c', 'fp', 'fp_author', 'lp', 'lp_author', 't_closedBy', 't_deletedBy', 't_stickiedBy'));

        $qb
            ->innerJoin('t.firstPost', 'fp')
                ->leftJoin('fp.createdBy', 'fp_author')
            ->leftJoin('t.lastPost', 'lp')
                ->leftJoin('lp.createdBy', 'lp_author')
            ->leftJoin('t.closedBy', 't_closedBy')
            ->leftJoin('t.deletedBy', 't_deletedBy')
            ->leftJoin('t.stickiedBy', 't_stickiedBy')
            ->leftJoin('t.board', 'b')
            ->leftJoin('b.category', 'c')
            ->where('t.isDeleted = :isDeleted')
            ->setParameters($params)
            ->orderBy('lp.createdDate', 'DESC');

        return $this->gateway->paginateQuery($qb, $this->getTopicsPerPageOnBoards(), $page);
    }
}