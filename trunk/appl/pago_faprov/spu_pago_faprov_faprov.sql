-------------------- [spu_pago_faprov_faprov] ---------------------------------
ALTER PROCEDURE [dbo].[spu_pago_faprov_faprov]
			(@ve_operacion					varchar(20)
			,@ve_cod_pago_faprov_faprov		numeric
			,@ve_cod_pago_faprov			numeric
			,@ve_cod_faprov					numeric = NULL
			,@ve_monto_asignado				T_PRECIO = NULL)

AS
BEGIN
		if (@ve_operacion='UPDATE') 
			begin
				UPDATE pago_faprov_faprov		
				SET		cod_pago_faprov		= @ve_cod_pago_faprov							
					   ,cod_faprov		    = @ve_cod_faprov
						 ,monto_asignado	= @ve_monto_asignado	
			
				WHERE cod_pago_faprov_faprov = @ve_cod_pago_faprov_faprov
						
			end
		else if (@ve_operacion='INSERT') 
			begin
				insert into pago_faprov_faprov
					(cod_pago_faprov
					,cod_faprov
					,monto_asignado)
				values 
					(@ve_cod_pago_faprov
					,@ve_cod_faprov
					,@ve_monto_asignado)
			end
		else if (@ve_operacion='DELETE_ALL') 
			begin
				delete pago_faprov_faprov
    			where cod_pago_faprov = @ve_cod_pago_faprov 
			end
		else if (@ve_operacion='RECALCULA')
			begin
				declare @vc_monto_asignado			numeric(18,0),
						@vc_saldo_sin_pago_faprov	numeric(18,0),
						@vc_cod_pago_faprov_faprov	numeric(18,0)
						
				declare c_pago_faprov cursor for
				select cod_pago_faprov_faprov
					  ,monto_asignado
					  ,dbo.f_pago_faprov_get_por_asignar(f.cod_faprov) + pff.monto_asignado saldo_sin_pago_faprov
				from pago_faprov_faprov pff
					,faprov f
				where pff.cod_faprov = f.cod_faprov
				and pff.cod_pago_faprov = @ve_cod_pago_faprov
				
				open c_pago_faprov 
				fetch c_pago_faprov into @vc_cod_pago_faprov_faprov, @vc_monto_asignado, @vc_saldo_sin_pago_faprov
				while @@fetch_status = 0 begin	
					
					if(@vc_monto_asignado > @vc_saldo_sin_pago_faprov)begin
						update pago_faprov_faprov
						set monto_asignado = 0
						where cod_pago_faprov_faprov = @vc_cod_pago_faprov_faprov
					end
	
					fetch c_pago_faprov into @vc_cod_pago_faprov_faprov, @vc_monto_asignado, @vc_saldo_sin_pago_faprov
				end
				close c_pago_faprov
				deallocate c_pago_faprov
				
				update PAGO_FAPROV
				set MONTO_DOCUMENTO = (select SUM(monto_asignado)
									   from pago_faprov_faprov
									   where cod_pago_faprov = @ve_cod_pago_faprov)
				where COD_PAGO_FAPROV = @ve_cod_pago_faprov
				
			end 	
END
go



