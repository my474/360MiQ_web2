function getHighstockMajorEvents(plotMajorEvents)
{
    var majorEvents = [];
    var tables = '<br><br><b>84 years</b> is the time <b>Uranus</b> takes to orbit the Sun.<br><table style="border: 1px solid grey;"><tr><td><b>Date</b></td><td><b>Event</b></td><td><b>Time Since Last Event</b></td></tr><tr><td>Dec 16, 1773</td><td>Boston Tea Party</td><td></td></tr><tr><td>Aug 24, 1857</td><td>Panic of 1857</td><td>83 years 8 months 8 days</td></tr><tr><td>Dec 7, 1941</td><td>Pearl Harbor Attack</td><td>84 years 3 months 13 days</td></tr></table><br><table style="border: 1px solid grey;"><tr><td><b>Date</b></td><td><b>Event</b></td><td><b>Time Since Last Event</b></td></tr><tr><td>Jul 4, 1776</td><td>Independence Day</td><td></td></tr><tr><td>Apr 12, 1861</td><td>Civil War Begins</td><td>84 years 9 months 8 days</td></tr><tr><td>Jun 6, 1944</td><td>WWII Ends</td><td>84 years 4 months 21 days</td></tr></table><br><table style="border: 1px solid grey;"><tr><td><b>Event Pair</b></td><td><b>Time Gap in Event Pair</b></td></tr><tr><td>Boston Tea Party — Independence Day</td><td>2 years 6 months 18 days</td></tr><tr><td>Panic of 1857 — Civil War Begins</td><td>3 years 7 months 19 days</td></tr><tr><td>Pearl Harbor Attack — WWII Ends</td><td>3 years 8 months 26 days</td></tr></table><br><b>When will be the next event pair?</b>';

    if (plotMajorEvents == 'US')
    {

        majorEvents = [
        // South Sea Bubble (1720)
        {
            id: 'southSeaBubble',
            from: Date.UTC(1711, 0, 1),  // January 1, 1711
            to: Date.UTC(1720, 7, 31),  // August 31, 1720
            color: 'rgba(255, 0, 0, 0.1)',  // Red for market events
            label: {
                text: 'South Sea<br>Bubble',
                y: 10,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: '<b>South Sea Bubble:</b> Jan,&nbsp;1711&nbsp;to&nbsp;Aug,&nbsp;1720<br>The South Sea Company established in January 1711 as a British joint-stock company. It offered to take over the national debt for its stock, leading to intense speculation with stock prices rising from approximately £100 in January to over £1,000 in August 1720. The bubble burst in September when the company couldn’t maintain the inflated stock value, causing a sharp decline and widespread financial losses. The crisis resulted in economic turmoil and the passage of the Bubble Act to prevent similar speculative ventures.<br><br>Isaac Newton lost a fortune in it, initially profiting as prices soared, but then re-investing at the peak, wiping out £20,000 (millions in today’s value). Disillusioned, he reportedly said he could "calculate the motions of the heavenly bodies, but not the madness of men."'
        },
        // Seven Years' War (1756–1763)
        {
            id: '7years',
            from: Date.UTC(1756, 4, 17), // May 17, 1756
            to: Date.UTC(1763, 1, 10), // February 10, 1763
            color: 'rgba(0, 0, 255, 0.1)', // Blue for wars
            label: {
                text: "Seven<br>Years'<br>War",
                y: 48,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>Seven Years' War:</b> May&nbsp;17,&nbsp;1756&nbsp;to&nbsp;Feb&nbsp;10,&nbsp;1763<br>A global conflict involving major European powers, primarily France and Britain, along with their allies. Fought in Europe, North America, Africa, and Asia, it was a struggle for colonial dominance. Britain's victory led to the acquisition of Canada and control over India, significantly enhancing its global influence. The war also marked the emergence of Prussia as a key player in European politics."
        },
        // Boston Tea Party (December 16, 1773)
        {
            id: 'bostonTea',
            from: Date.UTC(1773, 11, 1),  // December 1, 1773
            to: Date.UTC(1773, 11, 31),  // December 31, 1773
            color: 'rgba(255, 0, 0, 0.2)', // Red for market events
            label: {
                text: 'Boston<br>Tea<br>Party',
                verticalAlign: 'bottom',
                y: -29,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: '<b>Boston Tea Party:</b> Dec&nbsp;16,&nbsp;1773<br>On December 16, 1773, colonial protesters, dressed as Native Americans, dumped tea into Boston Harbor to oppose the British Tea Act of 1773. This act had granted the East India Company a monopoly on tea sales, which was perceived as an unjust tax. The protest galvanized opposition to British rule, leading to the First Continental Congress and eventually the Revolutionary War.' + tables
        },
        // US Revolutionary War (1775-1783)
        {
            id: 'revolution',
            from: Date.UTC(1775, 3, 19), // April 19, 1775
            to: Date.UTC(1783, 8, 3), // September 3, 1783
            color: 'rgba(0, 0, 255, 0.1)', // Blue for wars
            label: {
                text: 'Revolutionary<br>War',
                y: 10,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>Revolutionary War:</b> Apr&nbsp;19,&nbsp;1775&nbsp;to&nbsp;Sep&nbsp;3,&nbsp;1783<br>A war between Great Britain and its 13 American colonies. Seeking independence due to grievances over taxation and lack of representation, the colonies declared their independence in 1776. The war saw key battles like Bunker Hill, Trenton, and Yorktown, with George Washington leading the Continental Army. The Treaty of Paris in 1783 acknowledged the United States as an independent nation, laying the foundation for democratic governance." + tables
        },
        // Louisiana Purchase (1803)
        {
            id: 'louisianaPurchase',
            from: Date.UTC(1803, 3, 30),  // April 30, 1803
            to: Date.UTC(1803, 11, 20),  // December 20, 1803
            color: 'rgba(255, 0, 0, 0.15)', // Red for market events
            label: {
                text: 'Louisiana<br>Purchase',
                y: 48,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>Louisiana Purchase:</b> Apr&nbsp;30,&nbsp;1803&nbsp;to&nbsp;Dec&nbsp;20,&nbsp;1803<br>The Louisiana Purchase stemmed from France's control of North America, ceded to Spain in 1762, then returned in 1801, prompting U.S. concerns over Mississippi River trade access as France faced financial strain and war with Britain. The U.S. bought 828,000 square miles from France for $15 million, doubling its size, with the treaty signed on April 30, 1803, and ratified by the U.S. Senate in October 1803, with France transferring authority on December 20, 1803. It spurred economic growth, statehood (e.g., Louisiana in 1812), and westward expansion, most notably through the Lewis and Clark expedition commissioned by President Thomas Jefferson in 1804 to explore the territory."
        },
        // Embargo Act of 1807
        {
            id: 'embargo1807',
            from: Date.UTC(1807, 11, 1),  // December 1, 1807
            to: Date.UTC(1807, 11, 31),  // December 31, 1807
            color: 'rgba(255, 0, 0, 0.2)', // Red for market events
            label: {
                text: 'Embargo<br>Act<br>1807',
                verticalAlign: 'bottom',
                y: -29,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>Embargo Act of 1807:</b> Dec&nbsp;23,&nbsp;1807<br>A U.S. law that banned all foreign trade to pressure Britain and France into respecting American neutrality during their conflict. Enacted by President Thomas Jefferson, it aimed to stop the seizure of American ships by European powers. However, it caused significant economic hardship in the U.S., leading to its repeal in 1809. It was succeeded by the Non-Intercourse Act, which restricted trade only with Britain and France."
        },
        // War of 1812 (1812-1815)
        {
            id: 'war1812',
            from: Date.UTC(1812, 5, 18), // June 18, 1812
            to: Date.UTC(1815, 1, 18), // February 18, 1815
            color: 'rgba(0, 0, 255, 0.1)', // Blue for wars
            label: {
                text: 'War<br>1812',
                y: 10,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>War of 1812:</b> Jun&nbsp;18,&nbsp;1812&nbsp;to&nbsp;Feb&nbsp;18,&nbsp;1815<br>A conflict with Britain triggered by British interference with American trade and the impressment of sailors, the U.S. aimed to protect its maritime rights and potentially expand into Canada. The war saw battles across the U.S., Canada, and at sea. It concluded with the Treaty of Ghent in 1814, which restored pre-war boundaries. The American victory at the Battle of New Orleans in 1815 boosted national pride and marked the end of hostilities."
        },
        // Convention of 1818 (1818)
        {
            id: 'convention1818',
            from: Date.UTC(1818, 9, 1), // October 1, 1818
            to: Date.UTC(1818, 9, 31), // October 31, 1818
            color: 'rgba(255, 0, 0, 0.2)', // Red for market events
            label: {
                text: 'Convention<br>1818',
                verticalAlign: 'bottom',
                y: -99,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>Convention of 1818:</b> Oct&nbsp;20,&nbsp;1818<br>The U.S. and Britain signed the Convention of 1818 on October 20, resolving tensions after the War of 1812 by addressing North American boundary disputes. Negotiated by Albert Gallatin and British diplomats, it set the US-Canada border at the 49th parallel from the Lake of the Woods to the Rocky Mountains, leaving the Oregon Country west of the Rockies jointly occupied for ten years, renewable indefinitely. This facilitated peaceful trade and settlement, delaying Oregon’s division until the 1846 Oregon Treaty. It also secured US fishing rights off Newfoundland, easing maritime conflicts."
        },
        // Florida Acquisition (1819-1821)
        {
            id: 'floridaAcquisition',
            from: Date.UTC(1819, 1, 22), // February 22, 1819
            to: Date.UTC(1821, 1, 28), // February 28, 1821
            color: 'rgba(255, 0, 0, 0.1)', // Red for market events
            label: {
                text: 'Florida<br>Acquisition',
                verticalAlign: 'bottom',
                y: -68,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>Florida Acquisition:</b> Feb&nbsp;22,&nbsp;1819&nbsp;to&nbsp;Feb,&nbsp;1821<br>The U.S. acquired Florida from Spain via the Adams-Onis Treaty, also known as the Transcontinental Treaty, was signed on February 22, 1819, prompted by Spain's weakened control after the First Seminole War and American expansionist goals. Negotiated by John Quincy Adams, the U.S. gained East and West Florida. Spain did not receive compensation, except that the U.S. agreed to pay up to $5 million for damages caused by American citizens who had revolted against Spanish rule. Ratified in February, 1821, it defined the border between the two countries, allowing the U.S. to expand southward into Florida and westward to the Pacific Ocean, and ultimately leading to Florida’s statehood in 1845."
        },
        // Panic of 1837 (American, 1837-1844)
        {
            id: 'panic1837',
            from: Date.UTC(1837, 2, 17),  // March 17, 1837
            to: Date.UTC(1844, 2, 31),  // March 31, 1844
            color: 'rgba(255, 0, 0, 0.1)', // Red for market events
            label: {
                text: 'Panic<br>1837',
                verticalAlign: 'bottom',
                y: -17,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>Panic of 1837:</b> Mar&nbsp;17,&nbsp;1837&nbsp;to&nbsp;1844<br>A financial crisis triggering a six-year economic depression. It was caused by speculative investments in land and railroads, compounded by President Andrew Jackson's policies, particularly the Specie Circular, which destabilized the banking system. The crisis led to numerous bank failures, business bankruptcies, and widespread unemployment, resulting in significant social unrest and economic hardship."
        },
        // Texas Annexation (1845)
        {
            id: 'texasAnnexation',
            from: Date.UTC(1845, 2, 1), // March 1, 1845
            to: Date.UTC(1845, 11, 29), // December 29, 1845
            color: 'rgba(255, 0, 0, 0.15)', // Red for market events
            label: {
                text: 'Texas<br>Annexation',
                y: 74,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>Texas Annexation:</b> Mar&nbsp;1,&nbsp;1845&nbsp;to&nbsp;Dec&nbsp;29,&nbsp;1845<br>Texas, originally part of Spanish New Spain and later Mexico after 1821, saw an influx of American settlers in the 1820s under land grants, leading to the Texas Revolution and independence as the Republic of Texas in 1836. After 9 years as an independent republic, Texas sought US annexation. Facing British interest and domestic pressure, Congress passed a joint resolution on March 1, 1845, under President Tyler, admitting Texas as the 28th state, finalized on December 29, 1845. This triggered the Mexican-American War (1846–1848) over disputed borders."
        },
        // Mexican-American War (1846-1848)
        {
            id: 'MXUSWar',
            from: Date.UTC(1846, 3, 25), // April 25, 1846
            to: Date.UTC(1848, 1, 2), // February 2, 1848
            color: 'rgba(0, 0, 255, 0.1)', // Blue for wars
            label: {
                text: 'Mexican<br>American<br>War',
                y: 10,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>Mexican-American War:</b> Apr&nbsp;25,&nbsp;1846&nbsp;to&nbsp;Feb&nbsp;2,&nbsp;1848<br>A war stemmed from the U.S. annexation of Texas in 1845, a move Mexico viewed as aggression, and a dispute over the Texas-Mexico border. American forces achieved significant victories, including the capture of Mexico City. The Treaty of Guadeloupe Hidalogo in 1848 concluded the war, with Mexico ceding over 500,000 square miles of land to the U.S., including present-day California, Arizona, New Mexico, and parts of other states. This expansion greatly increased the size of the U.S. and set the stage for further westward development."
        },
        // Oregon Treaty (1846)
        {
            id: 'oregonTreaty',
            from: Date.UTC(1846, 5, 1), // June 1, 1846
            to: Date.UTC(1846, 5, 30), // June 30, 1846
            color: 'rgba(255, 0, 0, 0.2)', // Red for market events
            label: {
                text: 'Oregon<br>Treaty',
                verticalAlign: 'bottom',
                y: -68,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>Oregon Treaty:</b> Jun&nbsp;15,&nbsp;1846<br>The U.S. gained Oregon through the Oregon Treaty of 1846, resolving a border dispute with Britain over the Oregon Country, jointly occupied since the Convention of 1818, which allowed both nations to settle and trade without a fixed boundary. Driven by increased American settlement via the Oregon Trail and President Polk’s expansionist agenda, the treaty, signed on June 15, 1846, set the boundary at the 49th parallel from the Rockies to the Pacific, granting the U.S. land south of this line, including modern Oregon, while Britain retained areas north, like British Columbia. This led to the Oregon Territory’s creation in 1848 and statehood in 1859."
        },
        // Gadsden Purchase (1853-1854)
        {
            id: 'gadsdenPurchase',
            from: Date.UTC(1853, 11, 30), // December 30, 1853
            to: Date.UTC(1854, 3, 25), // April 25, 1854
            color: 'rgba(255, 0, 0, 0.2)', // Red for market events
            label: {
                text: 'Gadsden<br>Purchase',
                y: 104,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>Gadsden Purchase:</b> Dec&nbsp;30,&nbsp;1853&nbsp;to&nbsp;Apr&nbsp;25,&nbsp;1854<br>Following the Mexican-American War (1846–1848), the Treaty of Guadalupe Hidalgo left border ambiguities. Driven by the need for a southern railroad route, diplomat James Gadsden negotiated with Mexico’s President Santa Anna, signing the Gadsden Purchase treaty on December 30, 1853, for $10 million, ratified by the US Senate on April 25, 1854, acquiring land south of the Gila River. The purchase finalized the US-Mexico border, enabling railroad development and completing the contiguous southwest."
        },
        // Panic of 1857 (American, 1857-1858)
        {
            id: 'panic1857',
            from: Date.UTC(1857, 7, 24),  // July 1, 1857
            to: Date.UTC(1858, 2, 31),  // March 31, 1858
            color: 'rgba(255, 0, 0, 0.15)', // Red for market events
            label: {
                text: 'Panic<br>1857',
                verticalAlign: 'bottom',
                y: -17,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>Panic of 1857:</b> Aug&nbsp;24,&nbsp;1857&nbsp;to&nbsp;Spring&nbsp;of&nbsp;1858<br>A financial crisis in the U.S. caused by overinvestment in railroads and declining agricultural prices. It started with the failure of the Ohio Life Insurance and Trust Company in August 1857, leading to a chain of bank failures and business closures. The economic downturn increased social and economic tensions between the Northern and Southern states, particularly over issues of slavery and economic policy, further fueling the divisions that would lead to the Civil War." + tables
        },
        // Civil War (American, 1861-1865)
        {
            id: 'civilWar',
            from: Date.UTC(1861, 3, 12),  // April 12, 1861
            to: Date.UTC(1865, 3, 9),  // April 9, 1865
            color: 'rgba(0, 0, 255, 0.1)',  // Blue for wars
            label: {
                text: 'Civil<br>War',
                verticalAlign: 'bottom',
                y: -99,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>Civil War:</b> Apr&nbsp;12,&nbsp;1861&nbsp;to&nbsp;Apr&nbsp;9,&nbsp;1865<br>A conflict between the Northern Union and the Southern Confederacy over issues of slavery and states' rights. The war saw intense fighting across the nation, with significant battles at Bull Run, Antietam, Gettysburg, and Vicksburg. President Lincoln's Emancipation Proclamation in 1863 freed slaves in rebel states, shifting the war's aim to include abolition. The Union's victory led to the ratification of the 13th Amendment in 1865, which permanently abolished slavery in the U.S." + tables
        },
        // Alaska Purchase from Russia  (1867)
        {
            id: 'alaskaPurchase',
            from: Date.UTC(1867, 2, 30),  // March 30, 1867
            to: Date.UTC(1867, 9, 18),  // October 18, 1867
            color: 'rgba(255, 0, 0, 0.15)', // Red for market events
            label: {
                text: 'Alaska<br>Purchase',
                y: 48,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>Alaska Purchase:</b> Mar&nbsp;30,&nbsp;1867&nbsp;to&nbsp;Oct&nbsp;18,&nbsp;1867<br>The Alaska Purchase stemmed from Russia's waning interest in its North American territories. By the mid-19th century, Russia faced financial difficulties after the Crimean War and found it hard to defend Alaska, especially from potential British threats. The treaty was signed on March 30, 1867, and ratified by the U.S. Senate on May 15, 1867, with ownership transferring on October 18, 1867. The price was $7.2 million for 586,412 square miles. The discovery of gold in 1896 and later oil reserves made Alaska economically valuable. It became the 49th state in 1959."
        },
        // Hawaii Annexation (1893-1898)
        {
            id: 'hawaiiAnnexation',
            from: Date.UTC(1893, 0, 17), // January 17, 1893
            to: Date.UTC(1898, 6, 7), // July 7, 1898
            color: 'rgba(0, 0, 255, 0.1)', // Blue for wars
            label: {
                text: 'Hawaii<br>Annexation',
                y: 61,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>Hawaii Annexation:</b> Jan&nbsp;17,&nbsp;1893&nbsp;to&nbsp;Jul&nbsp;7,&nbsp;1898<br>The U.S. gained Hawaii through a joint resolution in 1898, following the overthrow of Queen Liliuokalani by American planters on January 17, 1893, supported by US Marines, driven by economic interests from the 1875 Reciprocity Treaty, which boosted sugar trade and leased Pearl Harbor, and the 1887 Bayonet Constitution, which weakened the monarchy amid growing American influence. After failed treaty attempts under President Cleveland, who opposed the overthrow, President McKinley annexed Hawaii via the Newlands Resolution on July 7, 1898, during the Spanish-American War, making it a territory and later the 50th state in 1959."
        },
        // Spanish-American War (1898)
        {
            id: 'ESUSWar',
            from: Date.UTC(1898, 3, 21), // April 21, 1898
            to: Date.UTC(1898, 11, 10), // December 10, 1898
            color: 'rgba(0, 0, 255, 0.15)', // Blue for wars
            label: {
                text: 'Spanish<br>American<br>War',
                y: 10,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>Spanish-American War:</b> Apr&nbsp;21,&nbsp;1898&nbsp;to&nbsp;Dec&nbsp;10,&nbsp;1898<br>A war between the U.S. and Spain over Cuban independence and Spanish colonial rule. Triggered by the sinking of the USS Maine in Havana Harbor, the U.S. declared war in April 1898. The war saw U.S. victories in both the Caribbean and the Pacific, leading to the Treaty of Paris in December 1898. Under the treaty, Spain ceded Puerto Rico, the Philippines, and other territories to the U.S., and recognized Cuban independence. This marked the emergence of the U.S. as a global power with significant territorial holdings."
        },
        // Panama Canal (1903)
        {
            id: 'panamaCanal',
            from: Date.UTC(1903, 10, 3),  // November 3, 1903
            to: Date.UTC(1914, 7, 15),  // August 15, 1914
            color: 'rgba(255, 0, 0, 0.1)', // Red for market events
            label: {
                text: 'Panama<br>Canal',
                verticalAlign: 'bottom',
                y: -61,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>Panama Canal:</b> Nov&nbsp;3,&nbsp;1903&nbsp;to&nbsp;Aug&nbsp;15,&nbsp;1914<br>In 1903, with U.S. support, Panama seceded from Colombia, setting the stage for canal construction. The Hay–Bunau-Varilla Treaty granted the U.S. control over the Canal Zone, allowing construction under the U.S. Army Corps of Engineers, which faced significant challenges such as disease, harsh terrain, and equipment limitations. The canal officially opened on August 15, 1914, with the SS Ancon making the inaugural passage. In 1977, the Torrijos–Carter Treaties were signed by President Jimmy Carter and Panamanian leader Omar Torrijos, laying the groundwork for Panama's eventual control of the canal, leading to the dissolution of the Panama Canal Zone in 1979 and a transition to joint U.S.-Panamanian administration. In 1996, Panama granted a concession to operate the ports of Balboa and Cristobal, on the Pacific and Atlantic sides of the Canal, respectively, to the Hong Kong company Hutchison-Whampoa. On December 31, 1999, Panama gained full sovereignty over the canal."
        },
        // Panic of 1907 (1907)
        {
            id: 'panic1907',
            from: Date.UTC(1907, 9, 14),  // October 14, 1907
            to: Date.UTC(1907, 10, 30),  // November 30, 1907
            color: 'rgba(255, 0, 0, 0.2)', // Red for market events
            label: {
                text: 'Panic<br>1907',
                verticalAlign: 'bottom',
                y: -17,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>Panic of 1907:</b> Oct&nbsp;14,&nbsp;1907&nbsp;to&nbsp;Nov&nbsp;6,&nbsp;1907<br>A financial crisis in the U.S. characterized by bank runs and near-collapses of major financial institutions. It was triggered by a failed attempt to corner the copper market, leading to a loss of confidence in the banking system. J.P. Morgan played a pivotal role in stabilizing the markets by organizing a group of bankers to provide necessary funds. The crisis underscored the need for a central bank, resulting in the establishment of the Federal Reserve System in 1913 to regulate and stabilize the U.S. economy."
        },
        // World War I (1914-1918)
        {
            id: 'wwi',
            from: Date.UTC(1914, 6, 28), // July 28, 1914
            to: Date.UTC(1918, 10, 11), // November 11, 1918
            color: 'rgba(0, 0, 255, 0.1)', // Blue for wars
            label: {
                text: 'WWI',
                y: 48,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>World War I:</b> Jul&nbsp;28,&nbsp;1914&nbsp;to&nbsp;Nov&nbsp;11,&nbsp;1918<br>A global conflict that involved many of the world's major powers divided into two opposing alliances: the Allies and the Central Powers. Characterized by trench warfare and the introduction of modern weaponry, it resulted in millions of casualties. The war ended with the armistice on November 11, 1918, and the subsequent Treaty of Versaille in 1919, which imposed severe penalties on Germany and redrew the boundaries of Europe. The treaty's harsh conditions sowed seeds of discontent that contributed to the outbreak of World War II."
        },
        // 1929 Market Crash (September 1929-July 1932)
        {
            id: 'crash1929',
            from: Date.UTC(1929, 8, 3), // September 3, 1929
            to: Date.UTC(1932, 6, 8), // July 8, 1932
            color: 'rgba(255, 0, 0, 0.1)', // Red for market events
            label: {
                text: '1929<br>Crash',
                verticalAlign: 'bottom',
                y: -17,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: '<b>1929 Market Crash:</b> Sep&nbsp;3,&nbsp;1929&nbsp;to&nbsp;Jul&nbsp;8,&nbsp;1932<br>October 29, 1929, known as "Black Tuesday", marked a catastrophic stock market crash that triggered the Great Depression. Following years of speculative investment and economic overgrowth during the Roaring Twenties, the stock market collapsed, leading to a severe economic downturn that lasted until the late 1930s. The Great Depression was characterized by high unemployment rates, bank failures, and a significant drop in industrial production and consumer spending, affecting economies worldwide.'
        },
        // World War II (1939-1945)
        {
            id: 'wwii',
            from: Date.UTC(1939, 8, 1), // September 1, 1939
            to: Date.UTC(1945, 8, 2), // September 2, 1945
            color: 'rgba(0, 0, 255, 0.1)', // Blue for wars
            label: {
                text: 'WWII',
                y: 10,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>World War II:</b> Sep&nbsp;1,&nbsp;1939&nbsp;to&nbsp;Sep&nbsp;2,&nbsp;1945<br>A global war that involved most of the world's nations, divided into two opposing alliances: the Allies and the Axis Powers. The war saw unprecedented destruction and loss of life, including the Holocaust and the use of atomic weapons. It concluded with the surrender of Germany and Japan to the Allies. In the aftermath, the United Nations was established to promote international cooperation and prevent future wars. The post-war period also saw the beginning of the Cold War between the U.S. and the Soviet Union, shaping global politics for decades." + tables
        },
        // Pearl Harbor Attack (December 7, 1941)
        {
            id: 'pearlHarbor',
            from: Date.UTC(1941, 11, 1),  // December 1, 1941
            to: Date.UTC(1941, 11, 31),  // December 31, 1941
            color: 'rgba(0, 0, 255, 0.2)', // Blue for wars
            label: {
                text: 'Pearl<br>Harbor<br>Attack',
                verticalAlign: 'bottom',
                y: -61,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>Pearl Harbor Attack:</b> Dec&nbsp;7,&nbsp;1941<br>A surprise Japanese attack on the U.S. naval base at Pearl Harbor, Hawaii. Intended to cripple the U.S. Pacific Fleet, the attack killed over 2,400 Americans and damaged or destroyed several naval vessels and aircraft. On the same day, Japan also launched coordinated attacks on the U.S.-held Philippines, Guam, and Wake Island, as well as on British possessions in Malaya, Singapore, and Hong Kong. This unprovoked act of aggression led President Franklin D. Roosevelt to declare war on Japan, bringing the U.S. into World War II and significantly altering the course of global events." + tables
        },
        // 1944 The Bretton Woods Agreement (July 1, 1944 - July 22, 1944)
        {
            id: 'brettonwoods',
            from: Date.UTC(1944, 6, 1), // July 1, 1944
            to: Date.UTC(1944, 6, 31), // July 22, 1944
            color: 'rgba(255, 0, 0, 0.1)', // Red for market events
            label: {
                text: 'Bretton<br>Woods<br>Agreement',
                y: 48,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: '<b>Bretton Woods Agreement:</b> Jul&nbsp;1,&nbsp;1944&nbsp;to&nbsp;Jul&nbsp;22,&nbsp;1944<br>The Bretton Woods Agreement was established during a conference held from July 1 to July 22, 1944, in Bretton Woods, New Hampshire. It created a new international monetary system that pegged major world currencies to the US dollar, which was in turn convertible to gold at a fixed rate of $35 per ounce. The goal was to ensure exchange rate stability, prevent competitive devaluations, and foster post-war economic recovery. The agreement led to the creation of the International Monetary Fund and the World Bank, both formally established in 1945. The system remained in place until August 15, 1971, when President Nixon ended dollar convertibility to gold, marking the collapse of the Bretton Woods system.'
        },
        // Korean War (1950-1953)
        {
            id: 'koreanWar',
            from: Date.UTC(1950, 5, 25), // June 25, 1950
            to: Date.UTC(1953, 6, 27), // July 27, 1953
            color: 'rgba(0, 0, 255, 0.1)', // Blue for wars
            label: {
                text: 'Korean<br>War',
                verticalAlign: 'bottom',
                y: -17,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>Korean War:</b> Jun&nbsp;25,&nbsp;1950&nbsp;to&nbsp;Jul&nbsp;27,&nbsp;1953<br>A conflict between North Korea (backed by China and the Soviet Union) and South Korea (supported by the United Nations, primarily the U.S.). It began when North Korea invaded South Korea on June 25, 1950, with the goal of reunifying the Korean Peninsula under communist rule. The UN intervened to repel the invasion, leading to 3 years of fighting that saw both sides making significant advances and retreats. The war ended with an armistice on July 27, 1953, establishing a demilitarized zone (DMZ) along the 38th parallel, which has since served as the de facto border between North and South Korea."
        },
        // Vietnam War (1965-1973, major U.S. involvement)
        {
            id: 'vietnamWar',
            from: Date.UTC(1965, 2, 2), // March 2, 1965
            to: Date.UTC(1973, 2, 29), // March 29, 1973
            color: 'rgba(0, 0, 255, 0.1)', // Blue for wars
            label: {
                text: 'Vietnam<br>War',
                y: 10,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>Vietnam War (major U.S. involvement):</b> Mar&nbsp;2,&nbsp;1965&nbsp;to&nbsp;Mar&nbsp;29,&nbsp;1973<br>A prolonged conflict between North Vietnam (communist) and South Vietnam (anti-communist), with significant involvement from the U.S. supporting South Vietnam. The war saw intense fighting and heavy casualties on both sides, as well as widespread protest in the U.S. against the war's conduct and morality. The Paris Peace Accords of 1973 facilitated the withdrawal of American troops. Two years later, North Vietnamese forces captured Saigon, leading to the reunification of Vietnam under communist rule."
        },
        // Oil Embargo (1973-1974)
        {
            id: 'oilEmbargo',
            from: Date.UTC(1973, 9, 2), // October 2, 1973
            to: Date.UTC(1974, 2, 29), // March 29, 1974
            color: 'rgba(255, 0, 0, 0.15)', // Red for market events
            label: {
                text: 'Oil<br>Embargo',
                verticalAlign: 'bottom',
                y: -17,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>Oil Embargo:</b> Oct&nbsp;2,&nbsp;1973&nbsp;to&nbsp;Mar&nbsp;29,&nbsp;1974<br>OPEC’s embargo in response to Western support for Israel during the Yom Kippur War. Starting in October 1973, OPEC reduced oil production and imposed an embargo on countries backing Israel, leading to a significant increase in oil prices—from approximately $3 per barrel to over $12 per barrel by December 1973. This caused fuel shortages and economic disruptions in many Western nations, contributing to inflation and economic stagnation throughout the 1970s."
        },
        // 1987 Market Crash (October 1987)
        {
            id: 'crash1987',
            from: Date.UTC(1987, 9, 1), // October 1, 1987
            to: Date.UTC(1987, 9, 31), // October 31, 1987
            color: 'rgba(255, 0, 0, 0.2)', // Red for market events
            label: {
                text: '1987<br>Crash',
                y: 48,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: '<b>1987 Market Crash:</b> Oct&nbsp;19,&nbsp;1987<br>October 19, 1987, known as "Black Monday", saw the Dow Jones Industrial Average plunge by 22.6%, one of the largest single-day drops in history. This severe decline was largely attributed to program trading, where computerized systems executed large sell orders automatically based on certain market conditions. The crash also reflected concerns about overvalued stocks and global economic instability. Its impact was felt worldwide, leading to increased regulatory oversight of financial markets and trading practices.'
        },
        // Gulf War (1990-1991)
        {
            id: 'gulfWar',
            from: Date.UTC(1991, 7, 2), // August 2, 1990
            to: Date.UTC(1991, 1, 28), // February 28, 1991
            color: 'rgba(0, 0, 255, 0.15)', // Blue for wars
            label: {
                text: 'Gulf<br>War',
                y: 10,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>The Gulf War:</b> Aug&nbsp;2,&nbsp;1990&nbsp;to&nbsp;Feb&nbsp;28,&nbsp;1991<br>The Gulf War saw the US lead a coalition to liberate Kuwait after Iraq's invasion in August 1990. Starting with air strikes on January 17, 1991, and followed by a ground offensive on February 24, the coalition quickly overwhelmed Iraqi forces, restoring Kuwait's sovereignty by March 3, 1991, under President George H. W. Bush."
        },
        // 2000 Tech Bubble Burst (March 2000-October 2002)
        {
            id: 'techBubble2000',
            from: Date.UTC(2000, 2, 10), // March 10, 2000
            to: Date.UTC(2002, 9, 10), // October 10, 2002
            color: 'rgba(255, 0, 0, 0.1)', // Red for market events
            label: {
                text: '2000<br>Tech<br>Bubble<br>Burst',
                verticalAlign: 'bottom',
                y: -43,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>2000 Tech Bubble Burst:</b> Mar&nbsp;10,&nbsp;2000&nbsp;to&nbsp;Oct&nbsp;10,&nbsp;2002<br>Also known as the dot-com bubble burst, a period of excessive speculation in technology stocks during the late 1990s. The NASDAQ Composite index reached its peak on March 10, 2000, and subsequently fell by approximately 78% by October 2002. This dramatic decline was driven by the realization that many internet-based companies were not profitable or sustainable, leading to bankruptcies and significant job losses. The burst of the tech bubble marked the end of an era of rapid growth and speculation in the technology sector, with long-term impacts on investment strategies and economic policy."
        },
        // 9/11 (September 11, 2001)
        {
            id: '9/11',
            from: Date.UTC(2001, 8, 1), // September 1, 2001
            to: Date.UTC(2001, 8, 30), // September 30, 2001
            color: 'rgba(0, 0, 255, 0.2)', // Blue for wars
            label: {
                text: '9/11<br>Attack',
                verticalAlign: 'bottom',
                y: -74,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>9/11 Attack:</b> Sep&nbsp;11,&nbsp;2001<br>A series of coordinated terrorist attacks carried out by al-Qaeda on September 11, 2001. Hijacked passenger jets were flown into the World Trade Center towers in New York City and the Pentagon in Washington, D.C., with a fourth plane crashing in Pennsylvania after passengers fought back against the hijackers. The attacks resulted in almost 3,000 deaths and profound changes in U.S. foreign policy and national security measures. In response, the U.S. launched the War on Terror, which included military operations in Afghanistan and Iraq, and implemented enhanced security protocols both domestically and internationally."
        },
        // Afghanistan  War (October 7, 2001 – August 30, 2021)
        {
            id: 'afghanistanWar',
            from: Date.UTC(2001, 9, 7), // October 7, 2001
            to: Date.UTC(2021, 7, 30), // August 30, 2021
            color: 'rgba(0, 0, 255, 0.1)', // Blue for wars
            label: {
                text: 'Afghanistan<br>War',
                verticalAlign: 'bottom',
                y: -99,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>The War in Afghanistan:</b> Oct&nbsp;7,&nbsp;2001&nbsp;to&nbsp;Aug&nbsp;30,&nbsp;2021<br>Initiated post-9/11, the US aimed to remove the Taliban and disrupt al-Qaeda. The invasion toppled the Taliban, but insurgency persisted, with US troops withdrawing in August 2021 after nearly two decades, ending direct combat involvement, bringing the country back under Taliban's rule."
        },
        // Iraqi War (March 20, 2003 – December 31, 2011)
        {
            id: 'iraqiWar',
            from: Date.UTC(2003, 2, 20), // March 20, 2003
            to: Date.UTC(2011, 11, 31), // December 31, 2011
            color: 'rgba(0, 0, 255, 0.1)', // Blue for wars
            label: {
                text: 'Iraqi<br>War',
                y: 104,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>Iraqi War:</b> Mar&nbsp;20,&nbsp;2003&nbsp;to&nbsp;Dec&nbsp;31,&nbsp;2011<br>Launched to overthrow Saddam Hussein and find weapons of mass destruction (none found), the US invaded in March 2003, capturing Baghdad by April. Post-invasion insurgency led to prolonged occupation, with combat troops withdrawn by December 2011, leaving advisory roles."
        },
        // 2008 Financial Crisis (2007-2009)
        {
            id: 'crisis2008',
            from: Date.UTC(2007, 9, 11), // October 11, 2007
            to: Date.UTC(2009, 2, 6), // March 6, 2009
            color: 'rgba(255, 0, 0, 0.1)', // Red for market events
            label: {
                text: '2008<br>Financial<br>Crisis',
                y: 10,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>2008 Financial Crisis:</b> Oct&nbsp;11,&nbsp;2007&nbsp;to&nbsp;Mar&nbsp;6,&nbsp;2009<br>A global economic meltdown that began with the collapse of the U.S. housing market and the subsequent failure of major financial institutions. Starting in late 2007, the crisis intensified in September 2008 with the bankruptcy of Lehman Brothers, leading to a severe credit crunch and economic recession worldwide. The crisis resulted in millions of job losses and trillions of dollars in lost wealth. In response, governments around the world enacted massive bailout packages and introduced regulatory reforms aimed at preventing future financial meltdowns and stabilizing the global economy."
        },
        // COVID-19 (March 2020-May 2023)
        {
            id: 'covid',
            from: Date.UTC(2020, 2, 1), // March 1, 2020
            to: Date.UTC(2023, 4, 30), // May 30, 2023
            color: 'rgba(255, 0, 0, 0.1)', // Red for market events
            label: {
                text: 'COVID',
                verticalAlign: 'bottom',
                y: -4,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: "<b>COVID-19:</b> Mar,&nbsp;2020&nbsp;to&nbsp;May,&nbsp;2023<br>A global health crisis caused by the SARS-CoV-2 virus, which emerged in Wuhan, China, in late 2019. Declared a pandemic by the World Health Organization in March 2020, it has infected hundreds of millions and caused millions of deaths worldwide. To curb its spread, governments implemented various measures such as lockdowns, social distancing, and mask mandates, leading to significant economic disruptions and changes in daily life. Vaccination campaigns were rolled out globally to mitigate the virus's impact."
        }
    ];
    }
    else if (plotMajorEvents == 'GOLD')
    {

        majorEvents = [
        // 1931 Gold Crash (Feb 1, 1931 - Dec 31, 1931)
        {
            id: 'goldstandardcrisis',
            from: Date.UTC(1931, 1, 1), // Feb 1, 1931
            to: Date.UTC(1931, 11, 31), // Dec 31, 1931
            color: 'rgba(255, 0, 0, 0.1)', // Red for market events
            label: {
                text: 'Gold<br>Standard<br>Crisis<br>1931',
                y: 48,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: '<b>Gold Standard Crisis of 1931:</b> Feb,&nbsp;1931&nbsp;to&nbsp;Dec,&nbsp;1931<br>The sharp fall in gold prices to $17.06 per ounce in early 1931 reflected the severe deflation and financial turmoil of the Great Depression. As the U.S. economy contracted following the 1929 stock market crash, the purchasing power of the dollar rose sharply, making the nominal price of gold appear lower while its real value remained relatively stable. Bank failures and liquidity shortages forced investors and institutions to sell gold to raise cash, temporarily flooding the market.<br><br><u>International effects</u>:<br>International uncertainty over adherence to the gold standard further contributed to price volatility. Later that year, on September 21, 1931, Britain officially abandoned the gold standard, intensifying global currency disruptions.<br><br><u>Policy response</u>:<br>These events set the stage for U.S. policy interventions, culminating in the Gold Reserve Act of 1934, which stabilized gold at $35 per ounce and marked the transition from a rigid gold standard to a managed currency system.'
        },
        // 1934 The Gold Reserve Act of 1934 (March 1, 1933 - January 1, 1934)
        {
            id: 'goldreserveact',
            from: Date.UTC(1933, 2, 1), // March 1, 1933
            to: Date.UTC(1934, 0, 1), // January 1, 1934
            color: 'rgba(255, 0, 0, 0.1)', // Red for market events
            label: {
                text: 'Gold<br>Reserve<br>Act',
                verticalAlign: 'bottom',
                y: -88,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: '<b>The Gold Reserve Act of 1934:</b> Mar&nbsp;6,&nbsp;1933&nbsp;to&nbsp;Jan&nbsp;31,&nbsp;1934<br>The Gold Reserve Act of 1934 was a cornerstone of U.S. monetary reform during the Great Depression. It followed a sequence of decisive measures that began when President Franklin D. Roosevelt declared a national banking holiday on March 6, 1933, suspending gold convertibility to stop bank runs. Soon after, Executive Order 6102 was issued on April 5, 1933, requiring all citizens and businesses to surrender their gold coins, bullion, and certificates to the Federal Reserve at $20.67 per ounce.<br><br><u>The Act and revaluation</u>:<br>Congress then passed the Gold Reserve Act on January 30, 1934, which transferred all gold and gold certificates to the U.S. Treasury and prohibited private ownership for monetary purposes. The next day, on January 31, 1934, the Treasury revalued gold to $35 per ounce, effectively devaluing the dollar by about 41 percent to combat deflation and revive exports.<br><br><u>Outcome and impact</u>:<br>This consolidation of gold holdings gave the Treasury full control over the nation’s reserves, expanded the money supply, and marked the transition from a rigid gold standard to a managed currency system that shaped U.S. monetary policy until the gold window was closed in 1971.'
        },
        // 1944 The Bretton Woods System (July 1, 1944 - August 15, 1971)
        {
            id: 'brettonwoods',
            from: Date.UTC(1944, 6, 1), // July 1, 1944
            to: Date.UTC(1971, 7, 15), // Aug 15, 1971
            color: 'rgba(255, 0, 0, 0.1)', // Red for market events
            label: {
                text: 'Bretton<br>Woods<br>System',
                y: 48,
                style: {
                    color: '#606060',
                    fontWeight: 'bold',
                    fontSize: '10px'
                }
            },
            zIndex: 4,
            tooltip: '<b>Bretton Woods System:</b> Jul&nbsp;1,&nbsp;1944&nbsp;to&nbsp;Aug&nbsp;15,&nbsp;1971<br>The Bretton Woods Agreement was established during a conference held from July 1 to July 22, 1944, in Bretton Woods, New Hampshire. It created a new international monetary system that pegged major world currencies to the US dollar, which was in turn convertible to gold at a fixed rate of $35 per ounce. The goal was to ensure exchange rate stability, prevent competitive devaluations, and foster post-war economic recovery. The agreement led to the creation of the International Monetary Fund and the World Bank, both formally established in 1945. The system remained in place until August 15, 1971, when President Nixon ended dollar convertibility to gold, marking the collapse of the Bretton Woods system.'
        }
    ];
    }

    majorEvents.forEach(function(event) {
        event.majorEventBaseColor = event.color;
        event.majorEventBaseLabelColor = event.label && event.label.style ? event.label.style.color : '#606060';
        applyHighstockMajorEventTheme(event);
    });

    return majorEvents;
}

function applyHighstockMajorEventTheme(event)
{
    var isDarkMode = document.documentElement.getAttribute('data-theme') === 'dark';
    var baseColor = event.majorEventBaseColor || event.color || 'rgba(128, 128, 128, 0.1)';
    var rgbaMatch = baseColor.match(/rgba\((\d+),\s*(\d+),\s*(\d+),\s*([\d.]+)\)/);

    if (isDarkMode && rgbaMatch)
    {
        var isBlueBand = parseInt(rgbaMatch[3], 10) > parseInt(rgbaMatch[1], 10);
        var darkRgb = isBlueBand ? [96, 165, 250] : [248, 113, 113];
        var darkOpacity = Math.min(0.26, Math.round((parseFloat(rgbaMatch[4]) + 0.08) * 100) / 100);
        event.color = 'rgba(' + darkRgb[0] + ', ' + darkRgb[1] + ', ' + darkRgb[2] + ', ' + darkOpacity + ')';
    }
    else
    {
        event.color = baseColor;
    }

    // Plot bands stay behind chart series in both themes.
    event.zIndex = 0;

    if (event.label)
    {
        event.label.style = event.label.style || {};
        event.label.style.color = isDarkMode ? '#dce3f4' : event.majorEventBaseLabelColor;
        event.label.style.textOutline = isDarkMode ? '1px rgba(15, 17, 28, 0.8)' : 'none';
    }

    return event;
}
