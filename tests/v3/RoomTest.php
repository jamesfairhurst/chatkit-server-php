<?php

class RoomTest extends \Base {

    public function testCreateRoomShouldRaiseAnExcepctionIfNoNameIsProvided()
    {
        $this->expectException(Chatkit\Exceptions\MissingArgumentException::class);
        $this->chatkit->createRoom([ 'creator_id' => 'ham' ]);
    }

    public function testCreateRoomShouldRaiseAnExcepctionIfNoCreatorIDIsProvided()
    {
        $this->expectException(Chatkit\Exceptions\MissingArgumentException::class);
        $this->chatkit->createRoom([ 'name' => 'my room' ]);
    }

    public function testCreateRoomShouldReturnAResponsePayloadIfACreatorIDAndNameAreProvided()
    {
        $user_id = $this->guidv4(openssl_random_pseudo_bytes(16));
        $user_res = $this->chatkit->createUser([
            'id' => $user_id,
            'name' => 'Ham'
        ]);
        $this->assertEquals($user_res['status'], 201);

        $room_res = $this->chatkit->createRoom([
            'creator_id' => $user_id,
            'name' => 'my room'
        ]);
        $this->assertEquals($room_res['status'], 201);
        $this->assertArrayHasKey('id', $room_res['body']);
        $this->assertEquals($room_res['body']['name'], 'my room');
        $this->assertFalse($room_res['body']['private']);
        $this->assertEquals($room_res['body']['member_user_ids'], [$user_id]);
    }

    public function testCreateRoomShouldReturnAResponsePayloadIfACreatorIDNameAndUserIDsAreProvidedAndTheRoomIsPrivate()
    {
        $user_id1 = $this->guidv4(openssl_random_pseudo_bytes(16));
        $user_id2 = $this->guidv4(openssl_random_pseudo_bytes(16));
        $user_res = $this->chatkit->createUsers([
            'users' => [
                [ 'id' => $user_id1, 'name' => 'Ham' ],
                [ 'id' => $user_id2, 'name' => 'Ham2' ]
            ]
        ]);
        $this->assertEquals($user_res['status'], 201);

        $room_res = $this->chatkit->createRoom([
            'creator_id' => $user_id1,
            'name' => 'my room',
            'user_ids' => [$user_id2],
            'private' => true
        ]);
        $this->assertEquals($room_res['status'], 201);
        $this->assertArrayHasKey('id', $room_res['body']);
        $this->assertEquals($room_res['body']['name'], 'my room');
        $this->assertTrue($room_res['body']['private']);
        $this->assertEmpty(array_diff($room_res['body']['member_user_ids'], [$user_id1, $user_id2]));
    }

    public function testCreateRoomShouldReturnAResponsePayloadIfACreatorIDNameAndCustomDataAreProvided() {
        $user_id = $this->guidv4(openssl_random_pseudo_bytes(16));
        $user_res = $this->chatkit->createUser([
            'id' => $user_id,
            'name' => 'Ham'
        ]);
        $this->assertEquals($user_res['status'], 201);

        $room_res = $this->chatkit->createRoom([
            'creator_id' => $user_id,
            'name' => 'my room',
            'custom_data' => array('foo' => 'bar')
        ]);
        $this->assertEquals($room_res['status'], 201);
        $this->assertArrayHasKey('id', $room_res['body']);
        $this->assertEquals($room_res['body']['name'], 'my room');
        $this->assertEquals($room_res['body']['custom_data'], array('foo' => 'bar'));
    }

    public function testUpdateRoomShouldRaiseAnExceptionIfNoIDIsProvided()
    {
        $this->expectException(Chatkit\Exceptions\MissingArgumentException::class);
        $this->chatkit->updateRoom([ 'name' => 'new name' ]);
    }

    public function testUpdateRoomShouldReturnAResponsePayloadIfAValidSetOfOptionsAreProvided()
    {
        $user_id = $this->guidv4(openssl_random_pseudo_bytes(16));
        $user_res = $this->chatkit->createUser([
            'id' => $user_id,
            'name' => 'Ham'
        ]);
        $this->assertEquals($user_res['status'], 201);

        $room_res = $this->chatkit->createRoom([
            'creator_id' => $user_id,
            'name' => 'my room',
            'custom_data' => array('foo' => 'bar')
        ]);
        $this->assertEquals($room_res['status'], 201);

        $update_res = $this->chatkit->updateRoom([
            'id' => $room_res['body']['id'],
            'name' => 'new name',
            'private' => true,
            'custom_data' => array('foo' => 'baz')
        ]);
        $this->assertEquals($update_res['status'], 204);
        $this->assertEquals($update_res['body'], null);

        $get_res = $this->chatkit->getRoom([
            'id' => $room_res['body']['id']
        ]);
        $this->assertEquals($get_res['status'], 200);
        $this->assertEquals($get_res['body']['name'], 'new name');
        $this->assertEquals($get_res['body']['private'], true);
        $this->assertEquals($get_res['body']['custom_data'], array('foo' => 'baz'));
    }

    public function testDeleteRoomShouldRaiseAnExceptionIfNoIDIsProvided()
    {
        $this->expectException(Chatkit\Exceptions\MissingArgumentException::class);
        $this->chatkit->deleteRoom([]);
    }

    public function testDeleteRoomShouldReturnAResponsePayloadIfAnIDIsProvided()
    {
        $user_id = $this->guidv4(openssl_random_pseudo_bytes(16));
        $user_res = $this->chatkit->createUser([
            'id' => $user_id,
            'name' => 'Ham'
        ]);
        $this->assertEquals($user_res['status'], 201);

        $room_res = $this->chatkit->createRoom([
            'creator_id' => $user_id,
            'name' => 'my room'
        ]);
        $this->assertEquals($room_res['status'], 201);

        $delete_res = $this->chatkit->deleteRoom([
            'id' => $room_res['body']['id']
        ]);
        $this->assertEquals($delete_res['status'], 204);
        $this->assertEquals($delete_res['body'], null);
    }

    public function testGetRoomShouldRaiseAnExceptionIfNoIDIsProvided()
    {
        $this->expectException(Chatkit\Exceptions\MissingArgumentException::class);
        $this->chatkit->getRoom([]);
    }

    public function testGetRoomShouldReturnAResponsePayloadIfAnIDIsProvided()
    {
        $user_id = $this->guidv4(openssl_random_pseudo_bytes(16));
        $user_res = $this->chatkit->createUser([
            'id' => $user_id,
            'name' => 'Ham'
        ]);
        $this->assertEquals($user_res['status'], 201);

        $room_res = $this->chatkit->createRoom([
            'creator_id' => $user_id,
            'name' => 'my room'
        ]);
        $this->assertEquals($room_res['status'], 201);

        $get_res = $this->chatkit->getRoom([
            'id' => $room_res['body']['id']
        ]);
        $this->assertEquals($get_res['status'], 200);
        $this->assertEquals($get_res['body']['name'], 'my room');
        $this->assertFalse($get_res['body']['private']);
        $this->assertEmpty(array_diff($get_res['body']['member_user_ids'], [$user_id]));
    }

    public function testGetRoomsShouldReturnAResponsePayloadIfNoOptionsAreProvided()
    {
        $user_id = $this->guidv4(openssl_random_pseudo_bytes(16));
        $user_res = $this->chatkit->createUser([
            'id' => $user_id,
            'name' => 'Ham'
        ]);
        $this->assertEquals($user_res['status'], 201);

        $room_res1 = $this->chatkit->createRoom([
            'creator_id' => $user_id,
            'name' => 'my room'
        ]);
        $this->assertEquals($room_res1['status'], 201);

        $room_res2 = $this->chatkit->createRoom([
            'creator_id' => $user_id,
            'name' => 'my second room'
        ]);
        $this->assertEquals($room_res2['status'], 201);

        $get_rooms_res = $this->chatkit->getRooms();
        $this->assertEquals($get_rooms_res['status'], 200);
        $this->assertEquals(count($get_rooms_res['body']), 2);
        $this->assertEquals($get_rooms_res['body'][0]['id'], $room_res1['body']['id']);
        $this->assertEquals($get_rooms_res['body'][0]['name'], 'my room');
        $this->assertFalse($get_rooms_res['body'][0]['private']);
        $this->assertEquals($get_rooms_res['body'][1]['id'], $room_res2['body']['id']);
        $this->assertEquals($get_rooms_res['body'][1]['name'], 'my second room');
        $this->assertFalse($get_rooms_res['body'][1]['private']);
    }

    public function testGetRoomsShouldReturnAResponsePayloadIfIncludePrivateIsSpecified()
    {
        $user_id = $this->guidv4(openssl_random_pseudo_bytes(16));
        $user_res = $this->chatkit->createUser([
            'id' => $user_id,
            'name' => 'Ham'
        ]);
        $this->assertEquals($user_res['status'], 201);

        $room_res1 = $this->chatkit->createRoom([
            'creator_id' => $user_id,
            'name' => 'my room',
            'private' => true
        ]);
        $this->assertEquals($room_res1['status'], 201);

        $room_res2 = $this->chatkit->createRoom([
            'creator_id' => $user_id,
            'name' => 'my second room'
        ]);
        $this->assertEquals($room_res2['status'], 201);

        $get_rooms_res1 = $this->chatkit->getRooms();
        $this->assertEquals($get_rooms_res1['status'], 200);
        $this->assertEquals(count($get_rooms_res1['body']), 1);
        $this->assertEquals($get_rooms_res1['body'][0]['id'], $room_res2['body']['id']);
        $this->assertEquals($get_rooms_res1['body'][0]['name'], 'my second room');
        $this->assertFalse($get_rooms_res1['body'][0]['private']);

        $get_rooms_res2 = $this->chatkit->getRooms([ 'include_private' => true ]);
        $this->assertEquals($get_rooms_res2['status'], 200);
        $this->assertEquals(count($get_rooms_res2['body']), 2);
        $this->assertEquals($get_rooms_res2['body'][0]['id'], $room_res1['body']['id']);
        $this->assertEquals($get_rooms_res2['body'][0]['name'], 'my room');
        $this->assertTrue($get_rooms_res2['body'][0]['private']);
        $this->assertEquals($get_rooms_res2['body'][1]['id'], $room_res2['body']['id']);
        $this->assertEquals($get_rooms_res2['body'][1]['name'], 'my second room');
        $this->assertFalse($get_rooms_res2['body'][1]['private']);
    }

    public function testGetRoomsShouldReturnAResponsePayloadIfIncludedPrivateAndFromIDAreSpecified()
    {
        $user_id = $this->guidv4(openssl_random_pseudo_bytes(16));
        $user_res = $this->chatkit->createUser([
            'id' => $user_id,
            'name' => 'Ham'
        ]);
        $this->assertEquals($user_res['status'], 201);

        $room_res1 = $this->chatkit->createRoom([
            'creator_id' => $user_id,
            'name' => 'my room',
            'private' => true
        ]);
        $this->assertEquals($room_res1['status'], 201);

        $room_res2 = $this->chatkit->createRoom([
            'creator_id' => $user_id,
            'name' => 'my second room'
        ]);
        $this->assertEquals($room_res2['status'], 201);

        $get_rooms_res1 = $this->chatkit->getRooms();
        $this->assertEquals($get_rooms_res1['status'], 200);
        $this->assertEquals(count($get_rooms_res1['body']), 1);
        $this->assertEquals($get_rooms_res1['body'][0]['id'], $room_res2['body']['id']);
        $this->assertEquals($get_rooms_res1['body'][0]['name'], 'my second room');
        $this->assertFalse($get_rooms_res1['body'][0]['private']);

        $get_rooms_res2 = $this->chatkit->getRooms([
            'include_private' => true,
            'from_id' => $room_res1['body']['id']
        ]);
        $this->assertEquals($get_rooms_res2['status'], 200);
        $this->assertEquals(count($get_rooms_res2['body']), 1);
        $this->assertEquals($get_rooms_res2['body'][0]['id'], $room_res2['body']['id']);
        $this->assertEquals($get_rooms_res2['body'][0]['name'], 'my second room');
        $this->assertFalse($get_rooms_res2['body'][0]['private']);

        $get_rooms_res3 = $this->chatkit->getRooms([
            'include_private' => true,
            'from_id' => $room_res2['body']['id']
        ]);
        $this->assertEquals($get_rooms_res3['status'], 200);
        $this->assertEquals(count($get_rooms_res3['body']), 0);
    }

    public function testGetUserRoomsShouldRaiseAnExceptionIfNoIDIsProvided()
    {
        $this->expectException(Chatkit\Exceptions\MissingArgumentException::class);
        $this->chatkit->getUserRooms([]);
    }

    public function testGetUserRoomsShouldReturnAResponsePayloadIfAnIDIsProvided()
    {
        $user_id = $this->guidv4(openssl_random_pseudo_bytes(16));
        $user_res = $this->chatkit->createUser([
            'id' => $user_id,
            'name' => 'Ham'
        ]);
        $this->assertEquals($user_res['status'], 201);

        $room_res = $this->chatkit->createRoom([
            'creator_id' => $user_id,
            'name' => 'my room'
        ]);
        $this->assertEquals($room_res['status'], 201);

        $get_user_rooms_res = $this->chatkit->getRooms([ 'id' => $user_id ]);
        $this->assertEquals($get_user_rooms_res['status'], 200);
        $this->assertEquals(count($get_user_rooms_res['body']), 1);
        $this->assertEquals($get_user_rooms_res['body'][0]['id'], $room_res['body']['id']);
        $this->assertEquals($get_user_rooms_res['body'][0]['name'], 'my room');
        $this->assertFalse($get_user_rooms_res['body'][0]['private']);
    }

    public function testGetUserRoomsShouldReturnAResponsePayloadIfAnIDIsProvidedAndOnlyReturnTheCorrectRooms()
    {
        $user_id = $this->guidv4(openssl_random_pseudo_bytes(16));
        $user_res = $this->chatkit->createUser([
            'id' => $user_id,
            'name' => 'Ham'
        ]);
        $this->assertEquals($user_res['status'], 201);

        $room_res = $this->chatkit->createRoom([
            'creator_id' => $user_id,
            'name' => 'my room'
        ]);
        $this->assertEquals($room_res['status'], 201);

        $user_id2 = $this->guidv4(openssl_random_pseudo_bytes(16));
        $user_res2 = $this->chatkit->createUser([
            'id' => $user_id2,
            'name' => 'Ham2'
        ]);
        $this->assertEquals($user_res2['status'], 201);

        $room_res2 = $this->chatkit->createRoom([
            'creator_id' => $user_id2,
            'name' => 'my second room'
        ]);
        $this->assertEquals($room_res2['status'], 201);

        $get_user_rooms_res = $this->chatkit->getUserRooms([ 'id' => $user_id ]);
        $this->assertEquals($get_user_rooms_res['status'], 200);
        $this->assertEquals(count($get_user_rooms_res['body']), 1);
        $this->assertEquals($get_user_rooms_res['body'][0]['id'], $room_res['body']['id']);
        $this->assertEquals($get_user_rooms_res['body'][0]['name'], 'my room');
        $this->assertFalse($get_user_rooms_res['body'][0]['private']);
    }

    public function testGetUserJoinableRoomsShouldRaiseAnExceptionIfNoIDIsProvided()
    {
        $this->expectException(Chatkit\Exceptions\MissingArgumentException::class);
        $this->chatkit->getUserJoinableRooms([]);
    }

    public function testGetUserJoinableRoomsShouldReturnAResponsePayloadIfAnIDIsProvided()
    {
        $user_id = $this->guidv4(openssl_random_pseudo_bytes(16));
        $user_res = $this->chatkit->createUser([
            'id' => $user_id,
            'name' => 'Ham'
        ]);
        $this->assertEquals($user_res['status'], 201);

        $room_res = $this->chatkit->createRoom([
            'creator_id' => $user_id,
            'name' => 'my room'
        ]);
        $this->assertEquals($room_res['status'], 201);

        $user_id2 = $this->guidv4(openssl_random_pseudo_bytes(16));
        $user_res2 = $this->chatkit->createUser([
            'id' => $user_id2,
            'name' => 'Ham2'
        ]);
        $this->assertEquals($user_res2['status'], 201);

        $get_user_rooms_res = $this->chatkit->getUserJoinableRooms([ 'id' => $user_id ]);
        $this->assertEquals($get_user_rooms_res['status'], 200);
        $this->assertEquals(count($get_user_rooms_res['body']), 0);

        $room_res2 = $this->chatkit->createRoom([
            'creator_id' => $user_id2,
            'name' => 'my second room'
        ]);
        $this->assertEquals($room_res2['status'], 201);

        $get_user_rooms_res = $this->chatkit->getUserJoinableRooms([ 'id' => $user_id ]);
        $this->assertEquals($get_user_rooms_res['status'], 200);
        $this->assertEquals(count($get_user_rooms_res['body']), 1);
        $this->assertEquals($get_user_rooms_res['body'][0]['id'], $room_res2['body']['id']);
        $this->assertEquals($get_user_rooms_res['body'][0]['name'], 'my second room');
        $this->assertFalse($get_user_rooms_res['body'][0]['private']);
    }

    public function testAddUsersToRoomShouldRaiseAnExceptionIfNoRoomIDIsProvided()
    {
        $this->expectException(Chatkit\Exceptions\MissingArgumentException::class);
        $this->chatkit->addUsersToRoom([ 'user_ids' => ['ham'] ]);
    }

    public function testAddUsersToRoomShouldRaiseAnExceptionIfNoUserIDsAreProvided()
    {
        $this->expectException(Chatkit\Exceptions\MissingArgumentException::class);
        $this->chatkit->addUsersToRoom([ 'room_id' => '123' ]);
    }

    public function testAddUsersToRoomShouldReturnAResponsePayloadIfARoomIDAndUserIDsAreProvided()
    {
        $user_id = $this->guidv4(openssl_random_pseudo_bytes(16));
        $user_res = $this->chatkit->createUser([
            'id' => $user_id,
            'name' => 'Ham'
        ]);
        $this->assertEquals($user_res['status'], 201);

        $user_id2 = $this->guidv4(openssl_random_pseudo_bytes(16));
        $user_res2 = $this->chatkit->createUser([
            'id' => $user_id2,
            'name' => 'Ham2'
        ]);
        $this->assertEquals($user_res2['status'], 201);

        $user_id3 = $this->guidv4(openssl_random_pseudo_bytes(16));
        $user_res3 = $this->chatkit->createUser([
            'id' => $user_id3,
            'name' => 'Ham3'
        ]);
        $this->assertEquals($user_res3['status'], 201);

        $room_res = $this->chatkit->createRoom([
            'creator_id' => $user_id,
            'name' => 'my room'
        ]);
        $this->assertEquals($room_res['status'], 201);

        $add_users_res = $this->chatkit->addUsersToRoom([
            'room_id' => $room_res['body']['id'],
            'user_ids' => [$user_id2, $user_id3]
        ]);
        $this->assertEquals($add_users_res['status'], 204);
        $this->assertEquals($add_users_res['body'], null);

        $get_res = $this->chatkit->getRoom([
            'id' => $room_res['body']['id']
        ]);
        $this->assertEquals($get_res['status'], 200);
        $this->assertEquals($get_res['body']['name'], 'my room');
        $this->assertFalse($get_res['body']['private']);
        $this->assertEmpty(array_diff($get_res['body']['member_user_ids'], [$user_id, $user_id2, $user_id3]));
    }

    public function testRemoveUsersFromRoomShouldRaiseAnExceptionIfNoRoomIDIsProvided()
    {
        $this->expectException(Chatkit\Exceptions\MissingArgumentException::class);
        $this->chatkit->removeUsersFromRoom([ 'user_ids' => ['ham'] ]);
    }

    public function testRemoveUsersFromRoomShouldRaiseAnExceptionIfNoUserIDsAreProvided()
    {
        $this->expectException(Chatkit\Exceptions\MissingArgumentException::class);
        $this->chatkit->removeUsersFromRoom([ 'room_id' => '123' ]);
    }

    public function testRemoveUsersFromRoomShouldReturnAResponsePayloadIfARoomIDAndUserIDsAreProvided()
    {
        $user_id = $this->guidv4(openssl_random_pseudo_bytes(16));
        $user_res = $this->chatkit->createUser([
            'id' => $user_id,
            'name' => 'Ham'
        ]);
        $this->assertEquals($user_res['status'], 201);

        $user_id2 = $this->guidv4(openssl_random_pseudo_bytes(16));
        $user_res2 = $this->chatkit->createUser([
            'id' => $user_id2,
            'name' => 'Ham2'
        ]);
        $this->assertEquals($user_res2['status'], 201);

        $user_id3 = $this->guidv4(openssl_random_pseudo_bytes(16));
        $user_res3 = $this->chatkit->createUser([
            'id' => $user_id3,
            'name' => 'Ham3'
        ]);
        $this->assertEquals($user_res3['status'], 201);

        $room_res = $this->chatkit->createRoom([
            'creator_id' => $user_id,
            'name' => 'my room',
            'user_ids' => [$user_id2, $user_id3]
        ]);
        $this->assertEquals($room_res['status'], 201);

        $remove_users_res = $this->chatkit->removeUsersFromRoom([
            'room_id' => $room_res['body']['id'],
            'user_ids' => [$user_id3]
        ]);
        $this->assertEquals($remove_users_res['status'], 204);
        $this->assertEquals($remove_users_res['body'], null);

        $get_res = $this->chatkit->getRoom([
            'id' => $room_res['body']['id']
        ]);
        $this->assertEquals($get_res['status'], 200);
        $this->assertEquals($get_res['body']['name'], 'my room');
        $this->assertFalse($get_res['body']['private']);
        $this->assertEmpty(array_diff($get_res['body']['member_user_ids'], [$user_id, $user_id2]));
    }
}
