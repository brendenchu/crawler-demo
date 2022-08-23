import React, {useEffect} from 'react';
import {Head, useForm} from '@inertiajs/inertia-react';
import Button from "@/Components/Button";
import Input from "@/Components/Input";
import Label from "@/Components/Label";

export default function Crawler(props) {
    const {data, setData, post, processing, errors, reset} = useForm({
        url: 'https://www.amazon.com',
        limit: 1,
    });

    useEffect(() => {
        return () => {
            reset('url', 'limit');
        };
    }, []);

    const onHandleChange = (event) => {
        setData(event.target.name, event.target.value);
    };

    const submit = (e) => {
        e.preventDefault();
        document.getElementById('crawler-results-header').innerHTML = 'Running. Please wait...';
        document.getElementById('crawler-results').innerHTML = '';
        axios.post('/api/crawl', data, {
            headers: {
                'X-CSRF-TOKEN': props.csrf,
            }
        }).then(res => {
            let crawledPages = res.data.data.crawledPages;

            let rows = '';
            for (let key in crawledPages) {
                rows = rows + "<tr class='border-gray-300'><td>" + crawledPages[key].url + "</td><td>" + crawledPages[key].status + "</td></tr>";
            }

            document.getElementById('crawler-results-header').innerHTML = 'Results';
            document.getElementById('crawler-results').innerHTML =
                "<div class='table-responsive'>" +
                "<h3 class='font-bold text-left'>Stats</h3>" +
                "<p>" +
                res.data.data.totalPagesCrawled + " pages crawled" + "<br />" +
                res.data.data.totalUniqueImages + " unique images" + "<br />" +
                res.data.data.totalInternalLinks + " total internal links" + "<br />" +
                res.data.data.totalExternalLinks + " total external links" + "<br />" +
                res.data.data.avgPageLoad + " seconds was the average page load time" + "<br />" +
                res.data.data.avgWordCount + " words was the average word count per page" + "<br />" +
                res.data.data.avgTitleLength + " characters was the average title length" + "<br />" +
                "</p>" +
                "</div><br />" +
                "<table class='table w-full'>" +
                "<thead><tr><th>Page URL</th><th>Status Code</th></tr></thead>" +
                "<tbody>" + rows + "</tbody>" +
                "</table>";

        }).catch(err => {
            document.getElementById('crawler-results-header').innerHTML = 'An error occurred. Please try again.';
        });


    };

    return (
        <>
            <Head title="Crawler Demo"></Head>
            <div className="flex justify-center">
                <div className="w-full">
                    <div className="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                        <h1 className="text-2xl font-bold text-center">Crawler Demo</h1>
                    </div>
                    <form onSubmit={submit}>
                        <div className="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                            <div className="mb-4">
                                <fieldset className="mb-6">
                                    <legend className="text-base font-medium text-gray-800">
                                        Start with an origin URL and crawl multiple randomly selected links from it.
                                    </legend>
                                    <br/>
                                    <Label forInput="url" value="Origin URL"/>
                                    <Input
                                        type="text"
                                        name="url"
                                        value={data.url}
                                        className="mt-1 block w-full"
                                        isFocused={true}
                                        handleChange={onHandleChange}
                                    />
                                    <br/>
                                    <Label forInput="limit" value="Page Limit"/>
                                    <select
                                        defaultValue={data.limit}
                                        onChange={onHandleChange}
                                        name="limit"
                                        className="w-full rounded-lg border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                                        <option value="0">Select a limit</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                        <option value="6">6</option>
                                    </select>
                                </fieldset>
                                <Button type='submit'>Start Crawler</Button>
                            </div>
                        </div>
                    </form>
                    <div className="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                        <h2 id="crawler-results-header" className="text-xl font-bold text-left">Results</h2><br />
                        <div id="crawler-results" className="text-left">None</div>
                    </div>
                </div>
            </div>
        </>
    )
        ;
}
