import React, {useEffect, useState} from 'react';
import PageClient from "Clients/PageClient";
import Loading from "Components/Elements/Loading";
import SubmitButton from "Components/Elements/SubmitButton";
import PageItem from "Components/Content/Levels/Levels/LevelsContent/PageItem";
import PagesFilter from "Components/Content/Levels/Levels/LevelsContent/PagesFilter";
import Paginator from "Components/Elements/Paginator";

function Pages({level}) {
  const pageClient = new PageClient();
  const [levelPageIds, setLevelPageIds] = useState(null);
  const [pages, setPages] = useState(null);
  const [paginatorPage, setPaginatorPage] = useState(1);
  const [paginatorItemsPerPage, setPaginatorItemsPerPage] = useState(25);
  const [filteredPages, setFilteredPages] = useState(null);
  const [displayedPageIds, setDisplayedPageIds] = useState(null);
  const [loadPages, setLoadPages] = useState(true);

  useEffect(() => {
    setLevelPageIds(null);
    setLoadPages(true);
  }, [level.id])

    useEffect(() => {
        if (filteredPages === null) {
            return;
        }

        setDisplayedPageIds(
            filteredPages.slice(
                paginatorPage * paginatorItemsPerPage - paginatorItemsPerPage,
                paginatorPage * paginatorItemsPerPage,
            ).map(page => page.id)
        )
    }, [paginatorPage, paginatorItemsPerPage, filteredPages]);

  useEffect(() => {
    const reloadPages = async () => {
      await pageClient.listWithCpts().then((data) => {
        setPages(data);

        if (filteredPages === null) {
          setFilteredPages(data);
        }
      });

      await pageClient.getIdsByLevel(level.id).then((data) => {
        setLevelPageIds(data);
      });

      setLoadPages(false);
    }

    if (loadPages === true) {
      reloadPages();
    }
  }, [loadPages]);

  const handleUpdatePages = async (event) => {
    event.preventDefault();
    const form = event.target;
    const selectedCheckboxes = form.querySelectorAll('.page-selected:checked');
    const pageIds = Array.from(selectedCheckboxes).map((checkbox) => {
      var id = checkbox.id.split('_')[1]

      if (isNaN(parseInt(id))) {
        return id;
      }

      return parseInt(id);
    });

    await pageClient.updatePagesForLevel(level.id, pageIds)
    setLoadPages(true);
  }

  if (pages === null || levelPageIds === null) {
    return (<Loading/>)
  } else {
    return (
        <form className="levels-content levels-pages" onSubmit={handleUpdatePages}>
          <PagesFilter
              pages={pages}
              setFilteredPages={setFilteredPages}
              assignedPageIds={levelPageIds}
              loadPages={loadPages}
          />
          <br/>

          <table>
            <thead>
                <tr>
                    <th></th>
                    <th>Název</th>
                    <th>Url</th>
                    <th>Typ</th>
                    <th>Přiřazeno</th>
                </tr>
            </thead>
            <tbody>
                {filteredPages.map(page => (
                    <PageItem
                      key={page.id}
                      page={page}
                      assigned={levelPageIds.includes(page.id)}
                      hidden={!displayedPageIds.includes(page.id)}
                    />
                ))}
                {pages.filter(item => !filteredPages.includes(item)).map(page => {
                    console.log(page)
                    return (
                        <PageItem
                            key={page.id}
                            page={page}
                            assigned={levelPageIds.includes(page.id)}
                            hidden={true}
                        />
                    );
                })}
            </tbody>
          </table>
            {filteredPages.length === 0
                ? (<p style={{textAlign: 'center'}}>Nebyly nalezeny žádné výsledky</p>)
                : null
            }
          <br/>
          <Paginator
              page={paginatorPage}
              setPage={setPaginatorPage}
              itemsPerPage={paginatorItemsPerPage}
              setItemsPerPage={setPaginatorItemsPerPage}
              itemCount={filteredPages.length}
          />
          <br/>
          <SubmitButton
              text="Uložit"
              style={{position: 'sticky'}}
              show={!loadPages}
              centered={true}
              big={true}
          />
        </form>
    )
  }
}

export default Pages;
