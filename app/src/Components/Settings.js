import React, { useState } from 'react';

import 'Styles/settings.css';
import 'Styles/global.css';

import Navigation from './Navigation/Navigation';
import SubNavigation from './Navigation/SubNavigation';
import Content from './Content/Content';

import logoFapi from 'Images/logo_fapi.svg';

import { SubNavItemType } from 'Enums/SubNavItemType';
import { NavItemType } from 'Enums/NavItemType';

import { NavigationFactory } from 'Services/NavigationFactory';
import Alert from "Components/Elements/Alert";

function Settings() {
    var url = new URL(window.location.href);


    const getNavItemsFromUrl = () => {
        url = new URL(window.location.href);
        var navItem =  url.searchParams.get('fm-page');
        var subNavItem =  url.searchParams.get('fm-sub-page');

        if (navItem === null) {
            navItem = NavItemType.OVERVIEW;
        }

        if (subNavItem === null) {
            subNavItem = navItem;
        }

        return {
            navItem: navItem,
            subNavItem: subNavItem,
        }
    }

    const [activeNavItem, setActiveNavItem] = useState(getNavItemsFromUrl().navItem);
    const [activeSubNavItem, setActiveSubNavItem] = useState(getNavItemsFromUrl().subNavItem);
    const navigation = new NavigationFactory().create();

    window.addEventListener('popstate', () => {
        handleNavItemChange(getNavItemsFromUrl().navItem, getNavItemsFromUrl().subNavItem, false);
    }, false);

    const handleNavItemChange = (navItem, subNavItem, redirect = true) => {
       if (redirect) {
           url = new URL(window.location.href);
           url.searchParams.set('fm-page', navItem);
           url.searchParams.delete('fm-levels-page')
           url.searchParams.delete('level')
           url.searchParams.delete('member')

           if (navItem !== subNavItem) {
                url.searchParams.set('fm-sub-page', subNavItem);
           } else {
                url.searchParams.delete('fm-sub-page');
           }

           window.history.pushState({fmPage: navItem}, document.title, url);
       }

        setActiveNavItem(navItem);
        setActiveSubNavItem(subNavItem);
    }

    return (
        <div>
            <Alert/>

            <div className="fm-settings">
                    <a className="fapi-logo" href="https://web.fapi.cz">
                        <img src={logoFapi} width="80"/>
                    </a>

                 <Navigation
                     navigation={navigation}
                     activeNavItem={activeNavItem}
                     onNavItemChange={handleNavItemChange}
                 />

                <SubNavigation
                    navigation={navigation}
                    activeNavItem={activeNavItem}
                    activeSubNavItem={activeSubNavItem}
                    onSubNavItemChange={handleNavItemChange}
                />

                <Content
                    navigation={navigation}
                    activeNavItem={activeNavItem}
                    activeSubNavItem={activeSubNavItem}
                />
            </div>
        </div>
    );
}

export default Settings;
