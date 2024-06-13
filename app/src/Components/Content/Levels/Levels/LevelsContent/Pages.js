import React, {useEffect, useState} from 'react';
import PageClient from "Clients/PageClient";
import Loading from "Components/Elements/Loading";
import SubmitButton from "Components/Elements/SubmitButton";
import PageItem from "Components/Content/Levels/Levels/LevelsContent/PageItem";

function Pages({level}) { //TODO: add CPT
  const pageClient = new PageClient();
  const [levelPageIds, setLevelPageIds] = useState(null);
  const [pages, setPages] = useState(null);
  const [loadPages, setLoadPages] = useState(true);

  useEffect(() => {
    setLevelPageIds(null);
    setLoadPages(true);
  }, [level.id])

  useEffect(() => {
    const reloadPages = async () => {
      await pageClient.listWithCpts().then((data) => {
        setPages(data);
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
          {
            [true, false].map(assigned => {
              const assignedPages = pages.filter(page => levelPageIds.includes(page.id) === assigned);

              if (assignedPages.length === 0) {
                return null;
              }

              return (
                <div key={assigned ? 'assigned' : 'unassigned'}>
                  <h2 className="text-center">{assigned ? 'Přiřazené' : 'Nepřiřazené'}</h2>

                  {[{value: 'post', title: 'Příspěvky'}, {value: 'page', title: 'Stránky',}, {value: 'cpt', title: 'CPT'}].map(type => {
                    const typePages = assignedPages.filter(page => page.type === type.value);

                    if (typePages.length === 0) {
                      return null;
                    }

                    return (
                      <div key={type.value}>
                        <h4>{type.title}</h4>
                        {typePages.map(page => (
                          <PageItem
                            key={page.id}
                            page={page}
                            checked={assigned}
                          />
                        ))}
                      </div>
                    );
                  })}
                  <br/>
                  <br/>
                </div>
              );
            })
          }
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
