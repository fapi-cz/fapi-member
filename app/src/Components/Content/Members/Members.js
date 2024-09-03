import 'Styles/members.css';

import React, {useEffect, useRef, useState} from 'react';

import UserClient from "Clients/UserClient";

import MembersFilter from "Components/Content/Members/MembersFilter";
import Paginator from "Components/Elements/Paginator";
import Loading from "Components/Elements/Loading";
import MemberItem from "Components/Content/Members/MemberItem";
import MemberSectionClient from "Clients/MemberSectionClient";
import Member from "Components/Content/Members/Member";
import SubmitButton from "Components/Elements/SubmitButton";
import {MemberService} from "Services/MemberService";

function Members() {
    const userClient = new UserClient();
    const sectionClient = new MemberSectionClient();
    const [paginatorPage, setPaginatorPage] = useState(1);
    const [paginatorItemsPerPage, setPaginatorItemsPerPage] = useState(25);
    const [filteredMembers, setFilteredMembers] = useState(null);
    const [members, setMembers] = useState(null);
    const [displayedMemberIds, setDisplayedMemberIds] = useState(null);
    const [levels, setLevels] = useState(null);
    const [loadMembers, setLoadMembers] = useState(true);
    const [activeMember, setActiveMember] = useState(null);
    const [exporting, setExporting] = useState(false);
    const [importing, setImporting] = useState(false);
    const importFile = useRef();

    useEffect(() => {
        if (filteredMembers === null) {
            return;
        }

        setDisplayedMemberIds(
            filteredMembers.slice(
                paginatorPage * paginatorItemsPerPage - paginatorItemsPerPage,
                paginatorPage * paginatorItemsPerPage,
            ).map(member => member.id)
        )
    }, [paginatorPage, paginatorItemsPerPage, filteredMembers]);

    useEffect(() => {
        const reloadMembers = async () => {
          await userClient.list(true).then((data) => {
            setMembers(data);

            updateActiveMemberFromUrl(data);

            if (filteredMembers === null) {
              setFilteredMembers(data);
            }
          });

          await sectionClient.getAllAsLevels().then((data) => {
              setLevels(data);
          });

          setLoadMembers(false);
        }

        if (loadMembers === true) {
          reloadMembers();
        }
    }, [loadMembers]);

    const updateActiveMemberFromUrl = (updatedMembers = members) => {
        if (updatedMembers === null) {
             return;
         }

        var url = new URL(window.location.href);
        var memberId = parseInt(url.searchParams.get('member'));

        if (!memberId) {
            setActiveMember(null);
            return;
        }

        updatedMembers.forEach((member) => {
            if (member.id === memberId) {
                setActiveMember(member);
                return;
            }
        });
    }

    const handleSetActiveMember = (member) => {
        var url = new URL(window.location.href);

        if (member !== null) {
            url.searchParams.set('member', member.id);
            window.history.pushState({member: member.id}, document.title, url);
        } else {
            url.searchParams.delete('member');
            window.history.pushState({member: null}, document.title, url);
            setLoadMembers(true);
        }

         updateActiveMemberFromUrl();
    }

    window.addEventListener('popstate', () => {
        updateActiveMemberFromUrl();
    }, false);

    const handleExport = async () => {
        setExporting(true);
        await MemberService.exportCsv(filteredMembers);
        setExporting(false);
    }

    const handleImport = async (event) => {
        setImporting(true);
        const file = event.target.files[0];
    
        if (file && file.type === 'text/csv') {
          const reader = new FileReader();
    
          reader.onload = async (e) => {
            await MemberService.importCsv(e.target.result).then(() => {
                setLoadMembers(true);
                setImporting(false);
            });
          };
    
          reader.readAsText(file);
          event.target.value = '';
        }
    };


    if (filteredMembers === null || displayedMemberIds === null || levels === null || loadMembers === true) {
        return (<Loading/>);
    }

    if (activeMember !== null) {
        return (
            <Member
                member={activeMember}
                removeActiveMember={() => (handleSetActiveMember(null))}
            />
        );
    }

    return (
        <div className="content-members">
            <h1 style={{marginBottom: '20px'}}>
                <strong>Členové</strong>

                <span style={{fontSize: '13px', width: 'max-content', display: 'flex', gap: '7px', float: 'right'}}>
                    <SubmitButton
                        text={'Exportovat (' + filteredMembers.length + ')'}
                        type={'light'}
                        onClick={handleExport}
                        show={!exporting}
                    />
                    <SubmitButton
                        text={'Importovat (csv)'}
                        type={'light'}
                        onClick={() => {importFile.current.click()}}
                        show={!importing}
                    />
                    <input
                        type="file"
                        ref={importFile}
                        style={{display: 'none'}}
                        accept=".csv"
                        onChange={handleImport}
                    />
                    {/*<SubmitButton text={'Vytvořit'}/>*/}
                </span>
            </h1>
            <MembersFilter
                members={members}
                setFilteredMembers={setFilteredMembers}
                levels={levels}
                loadMembers={loadMembers}
            />
            <br/>
            <table>
                <thead>
                    <tr>
                        <th></th>
                        <th>Email</th>
                        <th>Jméno a příjmení</th>
                        <th>Členství</th>
                        <th>Datum registrace</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    {filteredMembers.map(member => (
                        <MemberItem
                            key={member.id}
                            member={member}
                            setActiveMember={handleSetActiveMember}
                            hidden={!displayedMemberIds.includes(member.id)}
                        />
                    ))}
                </tbody>
            </table>
            {filteredMembers.length === 0
                ? (<p style={{textAlign: 'center'}}>Nebyly nalezeny žádné výsledky</p>)
                : null
            }
            <br/>
            <Paginator
                page={paginatorPage}
                setPage={setPaginatorPage}
                itemsPerPage={paginatorItemsPerPage}
                setItemsPerPage={setPaginatorItemsPerPage}
                itemCount={filteredMembers.length}
            />
        </div>
    );
}

export default Members;
