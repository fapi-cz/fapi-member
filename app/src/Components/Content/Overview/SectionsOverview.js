import React, {useEffect, useState} from 'react';
import MemberSectionClient from "Clients/MemberSectionClient";
import Loading from "Components/Elements/Loading";
import PieChartWidget from "Components/Elements/PieChartWidget";
import {StringHelper} from "Helpers/StringHelper";
import LevelsOverview from "Components/Content/Overview/LevelsOverview";
import MembershipClient from "Clients/MembershipClient";
import StatisticsBar from "Components/Content/Overview/StatisticsBar";
import PageClient from "Clients/PageClient";

function SectionsOverview() {
    const [loading, setLoading] = useState(true);
    const [sections, setSections] = useState(null);
    const [pagesByLevel, setPagesByLevel] = useState(null);
    const [membershipsByUsers, setMembershipsByUsers] = useState(null);
    const [activeSection, setActiveSection] = useState(null)

    const memberSectionClient = new MemberSectionClient();
    const membershipClient = new MembershipClient();
    const pageClient = new PageClient();

     useEffect(() => {
         const reloadData = async () => {
            var updatedSections = await memberSectionClient.getAll();
            setSections(updatedSections);

            var updatedMemberships = await membershipClient.getAll();
            setMembershipsByUsers(updatedMemberships);

            var updatedPages = await pageClient.getIdsByAllLevels();
            setPagesByLevel(updatedPages);

            setLoading(false);
        }

        if(loading === true) {
           reloadData();
        }
     }, [loading]);

     const countActiveUsersByLeveId = (levelId) => {
         var activeUsers = 0;

         membershipsByUsers.forEach((membershipsByUser) => {
             if (membershipsByUser[levelId] !== undefined) {
                 activeUsers++;
             }
         });

         return activeUsers;
    }

     const sectionsToChartData = () => {
         return sections.map((section) => {
            return {name: section.name, value: countActiveUsersByLeveId(section.id)};
         })
     }

     if (sections === null || membershipsByUsers === null || pagesByLevel === null) {
         return (<Loading/>);
     }

     if (activeSection === null) {
         return (
             <div className="content-sections-overview">
                 <h1 style={{marginBottom: '20px'}}><strong>Přehled členských sekcí</strong></h1>
                 <StatisticsBar
                    columns={[
                        {label: 'Počet členů celkem', value: membershipsByUsers.length},
                        {label: 'Přiřazených stránek celkem', value: Object.values(pagesByLevel).flat().length},
                        {label: 'Počet sekcí', value: sections.length},
                    ]}
                 />
                 <h2 style={{marginBottom: '10px'}}>Sekce:</h2>
                 <div className='levels-overview' id='sections-overview'>
                     <div className='levels-overview-list'>
                         {sections.map((section) => (
                             <div
                                 key={section.id}
                                 className='levels-overview-item clickable-option'
                                 onClick={() => {setActiveSection(section)}}
                             >
                                 <div
                                     className='color'
                                     style={
                                         {backgroundColor: StringHelper.stringToColor(section.name)}
                                     }
                                 />
                                 <span className='name'>{StringHelper.truncateText(section.name, 38)}</span>
                             </div>
                         ))}
                     </div>
                    <PieChartWidget
                        data={sectionsToChartData()}
                        title={'Počet členů'}
                    />
                 </div>
             </div>
         );
     }

     return (
       <LevelsOverview
           section={activeSection}
           setActiveSection={setActiveSection}
           countActiveUsersByLeveId={countActiveUsersByLeveId}
           pagesByLevel={pagesByLevel}
       />
     );
}

export default SectionsOverview;
