<?php



namespace Entity;

/**
 * SearchFlightsQueries
 *
 * @Table(name="search_flights_queries")
 * @Entity
 */
class SearchFlightsQueries
{
    /**
     * @var integer
     *
     * @Column(name="id", type="bigint", nullable=false)
     * @Id
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @Column(name="name", type="string", length=200, nullable=true)
     */
    private $name;

    /**
     * @var integer
     *
     * @Column(name="fdr", type="integer", nullable=true)
     */
    private $fdr;

    /**
     * @var string
     *
     * @Column(name="alg", type="text", length=65535, nullable=true)
     */
    private $alg;

    /**
     * @var integer
     *
     * @Column(name="authorId", type="integer", nullable=true)
     */
    private $authorid;


}
