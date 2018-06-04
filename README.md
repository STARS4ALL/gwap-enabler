# GWAP Enabler

The GWAP Enabler is an open source software framework that allows to design and develop GWAP web applications to solve linked data refinement issues. 

## The problem
With the rise of linked data  and  knowledge graphs, the need becomes compelling to find suitable solutions to increase the coverage and correctness of datasets, to add missing knowledge and to identify and remove errors. Several approaches - mostly relying on machine learning and NLP techniques - have been proposed to address this _refinement_ goal; they usually need a _partial gold standard_, i.e. some "ground truth" to train automatic models. Gold standards are manually constructed, either by involving domain experts or by adopting crowdsourcing and human computation solutions.


## The solution
The GWAP Enabler is an open source software framework to build _Games with a Purpose for linked data refinement_, i.e. web applications to crowdsource partial ground truth, by motivating user participation through fun incentive. The GWAP Enabler addresses specific _data linking_ "purposes" (creation, ranking and validation of links) by embedding the respective _crowdsourcing tasks_ to achieve those goals within the gameplay. 

The GWAP Enabler has been adopted to implement a set of _diverse applications_ (e.g. [Indomilando](http://bit.ly/indomilando), [Land Cover Validation Game](http://bit.ly/foss4game), [Night Knights](http://www.nightknights.eu/)) that demonstrate its reusability and extensibility potential; detailed documentation is available, including an entire [_tutorial_](https://github.com/STARS4ALL/gwap-enabler-tutorial) which in a few hours guides new adopters to customize and adapt the framework to a new use case.

## Documentation
Technical details, including data models are available in [this project's wiki](https://github.com/STARS4ALL/gwap-enabler/wiki).<br>
API documentation is available on [Apiary](https://gwapenablerapi.docs.apiary.io/).<br>
A full tutorial is available in [another Github project](https://github.com/STARS4ALL/gwap-enabler-tutorial).

## Citation
To cite the GWAP Enabler, please use the following reference:<br>
> Gloria Re Calegari, Andrea Fiano, Irene Celino: A Framework to build Games with a Purpose for Linked Data Refinement, in proceedings of the International Semantic Web Conference 2018, Resources Track, Monterey, California, 2018.

The design and development of the GWAP Enabler has been partially supported by the EU H2020 project [STARS4ALL](http://www.stars4all.eu/).
